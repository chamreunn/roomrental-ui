<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Utils\Util;
use App\Enum\Active;
use App\Enum\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        // === 1. Get filter/search params ===
        $statusFilter   = $request->query('status');
        $search         = $request->query('search');
        $locationFilter = $request->query('location');
        $buildingFilter = $request->query('building_name');
        $floorFilter    = $request->query('floor_name');
        $roomFilter     = $request->query('room_name');
        $roomTypeFilter = $request->query('room_type');
        $monthFilter    = $request->query('month');
        $fromDate       = $request->query('from_date');
        $toDate         = $request->query('to_date');

        // === 2. Fetch invoices from API ===
        $response = $this->api()->get('v1/invoices');
        $invoices = $response['data'] ?? [];

        $totals = [
            'room_fee'        => $response['total_room_fee'] ?? 0,
            'electric_charge' => $response['total_electric_charge'] ?? 0,
            'water_charge'    => $response['total_water_charge'] ?? 0,
        ];

        // === 3. Apply Filters ===

        // Status filter
        if ($statusFilter !== null && $statusFilter !== '') {
            $invoices = array_filter($invoices, fn($inv) => $inv['status'] == $statusFilter);
        }

        // Location filter
        if ($locationFilter !== null && $locationFilter !== '') {
            $invoices = array_filter(
                $invoices,
                fn($inv) => ($inv['room']['location_id'] ?? null) == $locationFilter
            );
        }

        // Building name filter
        if ($buildingFilter) {
            $invoices = array_filter(
                $invoices,
                fn($inv) =>
                str_contains(strtolower($inv['room']['building_name'] ?? ''), strtolower($buildingFilter))
            );
        }

        // Floor name filter
        if ($floorFilter) {
            $invoices = array_filter(
                $invoices,
                fn($inv) =>
                str_contains(strtolower($inv['room']['floor_name'] ?? ''), strtolower($floorFilter))
            );
        }

        // Room name filter
        if ($roomFilter) {
            $invoices = array_filter(
                $invoices,
                fn($inv) =>
                str_contains(strtolower($inv['room']['room_name'] ?? ''), strtolower($roomFilter))
            );
        }

        // Room type filter
        if ($roomTypeFilter !== null && $roomTypeFilter !== '') {
            $invoices = array_filter(
                $invoices,
                fn($inv) => ($inv['room']['room_type_id'] ?? null) == $roomTypeFilter
            );
        }

        // Month filter (YYYY-MM)
        if ($monthFilter) {
            $invoices = array_filter($invoices, function ($inv) use ($monthFilter) {
                return \Carbon\Carbon::parse($inv['invoice_date'])->format('Y-m') === $monthFilter;
            });
        }

        // Date range filter
        if ($fromDate && $toDate) {
            $invoices = array_filter($invoices, function ($inv) use ($fromDate, $toDate) {
                $date = \Carbon\Carbon::parse($inv['invoice_date'])->toDateString();
                return $date >= $fromDate && $date <= $toDate;
            });
        }

        // General search
        if ($search) {
            $search = strtolower($search);
            $invoices = array_filter($invoices, function ($inv) use ($search) {
                return str_contains(strtolower($inv['invoice_no']), $search) ||
                    str_contains(strtolower($inv['room']['room_name'] ?? ''), $search);
            });
        }

        // === 4. Fetch dropdown data ===
        $locations  = $this->api()->get('v1/locations')['locations']['data'] ?? [];
        $roomTypes  = $this->api()->get('v1/room-types')['room_types']['data'] ?? [];

        // === 5. Return view ===
        return view('app.invoices.index', [
            'invoices'         => $invoices,
            'totals'           => $totals,
            'filter_status'    => $statusFilter,
            'filter_location'  => $locationFilter,
            'filter_building'  => $buildingFilter,
            'filter_floor'     => $floorFilter,
            'filter_room'      => $roomFilter,
            'filter_room_type' => $roomTypeFilter,
            'filter_month'     => $monthFilter,
            'from_date'        => $fromDate,
            'to_date'          => $toDate,
            'search'           => $search,
            'locations'        => $locations,
            'roomTypes'        => $roomTypes,
        ]);
    }

    public function chooseLocation(Request $request)
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.invoices.choose-loation', compact('locations'));
    }

    public function chooseRoom(Request $request, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('invoice.choose_location'),
            ],
        ];

        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'purple', 'teal', 'orange'];

        try {
            $perPage = $request->query('per_page', 10);
            $currentPage = $request->query('page', 1);

            // ✅ Correct API call (header is the 4th argument)
            $response = $this->api()->get('v1/rooms', $request->query(), null, [
                'location_id' => $locationId,
            ]);

            // ✅ Extract rooms data correctly
            $roomsData = $response['rooms'] ?? [];

            // ✅ Use the API’s own pagination data if available
            $paginatedData = $roomsData['data'] ?? [];
            $total = $roomsData['total'] ?? count($paginatedData);
            $perPage = $roomsData['per_page'] ?? $perPage;
            $currentPage = $roomsData['current_page'] ?? $currentPage;

            // ✅ Transform each room entry
            $dataCollection = collect($paginatedData)->transform(function ($item) {
                $item['status_badge'] = RoomStatus::getStatus($item['status']); // Use 'status' not 'is_active'
                $item['create_date_kh'] = Util::translateDateToKhmer($item['created_at'], 'd F, Y h:i A');
                $item['update_date_kh'] = Util::translateDateToKhmer($item['updated_at'], 'd F, Y h:i A');

                // Optional: attach location and type info more conveniently
                $item['location_name'] = $item['location']['location_name'] ?? '-';
                $item['type_name'] = $item['room_type']['type_name'] ?? '-';
                $item['room_size'] = $item['room_type']['room_size'] ?? '-';
                $item['price'] = $item['room_type']['price'] ?? '-';

                return $item;
            });

            // ✅ Manual pagination for the frontend
            $rooms = new LengthAwarePaginator(
                $dataCollection,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => url()->current(),
                    'query' => $request->query(),
                ]
            );
        } catch (Exception $e) {
            // Handle gracefully if API fails
            $rooms = new LengthAwarePaginator([], 0, 10);
        }

        return view('app.invoices.choose-room', compact('buttons', 'rooms', 'locationId', 'colors'));
    }

    public function create(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('invoice.choose_room', $locationId),
            ],
        ];

        $response = $this->api()
            ->withHeaders(['location_id' => $locationId])
            ->get('v1/rooms/' . $roomId);

        $room = $response['room'];

        // ✅ Safely prepare clients with formatted data
        $clients = collect($room['clients'] ?? [])->map(function ($client) {
            $client['clientstatus'] = Active::getStatus($client['status']);
            $client['dateOfBirth'] = Carbon::parse($client['date_of_birth'])->translatedFormat('d F Y');
            $client['start_rental_date'] = Carbon::parse($client['start_rental_date'])->translatedFormat('d F Y');
            return $client;
        });

        return view('app.invoices.create', compact('room', 'clients', 'buttons'));
    }

    public function preview(Request $request, $roomId, $locationId)
    {
        // === 1. Validate input ===
        $validator = Validator::make($request->all(), [
            'month'          => 'required|string',
            'old_electric'   => 'required|numeric|min:0',
            'new_electric'   => 'required|numeric|min:0',
            'electric_rate'  => 'required|numeric|min:0',
            'old_water'      => 'required|numeric|min:0',
            'new_water'      => 'required|numeric|min:0',
            'water_rate'     => 'required|numeric|min:0',
            'other_charge'   => 'nullable|numeric|min:0',
        ], [
            // === 3. Custom message translations ===
            'month.required'         => __('validation.required_month'),
            'old_electric.required'  => __('validation.required_old_electric'),
            'new_electric.required'  => __('validation.required_new_electric'),
            'electric_rate.required' => __('validation.required_electric_rate'),
            'old_water.required'     => __('validation.required_old_water'),
            'new_water.required'     => __('validation.required_new_water'),
            'water_rate.required'    => __('validation.required_water_rate'),
        ]);

        // === 2. Custom validation logic ===
        $validator->after(function ($validator) use ($request) {
            if ($request->new_electric < $request->old_electric) {
                $validator->errors()->add('new_electric', __('validation.new_electric_must_be_greater'));
            }
            if ($request->new_water < $request->old_water) {
                $validator->errors()->add('new_water', __('validation.new_water_must_be_greater'));
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // === 3. Get room and clients from API ===
        $response = $this->api()
            ->withHeaders(['location_id' => $locationId])
            ->get('v1/rooms/' . $roomId);

        $room = $response['room'];

        // ✅ Safely prepare clients with formatted data
        $clients = collect($room['clients'] ?? [])->map(function ($client) {
            $client['clientstatus'] = Active::getStatus($client['status']);
            $client['dateOfBirth'] = Carbon::parse($client['date_of_birth'])->translatedFormat('d F Y');
            $client['start_rental_date'] = Carbon::parse($client['start_rental_date'])->translatedFormat('d F Y');
            return $client;
        });

        // === 4. Cast numeric values safely ===
        $oldElectric   = (float) str_replace(',', '', $request->old_electric);
        $newElectric   = (float) str_replace(',', '', $request->new_electric);
        $electricRate  = (float) str_replace(',', '', $request->electric_rate);
        $oldWater      = (float) str_replace(',', '', $request->old_water);
        $newWater      = (float) str_replace(',', '', $request->new_water);
        $waterRate     = (float) str_replace(',', '', $request->water_rate);
        $otherCharge   = (float) str_replace(',', '', $request->other_charge ?? 0);

        // === 5. Calculate usage ===
        $electricUsed = max(0, $newElectric - $oldElectric);
        $waterUsed    = max(0, $newWater - $oldWater);

        // === 6. Calculate subtotals ===
        $electricTotal = $electricUsed * $electricRate;
        $waterTotal    = $waterUsed * $waterRate;
        $roomRent      = isset($room['room_type']['price']) ? (float) $room['room_type']['price'] : 0;

        // === 7. Grand total (match your calculateTotal method) ===
        $grandTotal = $roomRent + $electricTotal + $waterTotal + $otherCharge;

        // === 8. Return view with preview data ===
        return view('app.invoices.create', [
            'room' => $room,
            'clients' => $clients,
            'preview' => [
                'month'            => $request->month,
                'old_electric'     => $oldElectric,
                'new_electric'     => $newElectric,
                'electric_rate'    => $electricRate,
                'electric_usage'   => $electricUsed,
                'electric_total'   => $electricTotal,
                'old_water'        => $oldWater,
                'new_water'        => $newWater,
                'water_rate'       => $waterRate,
                'water_usage'      => $waterUsed,
                'water_total'      => $waterTotal,
                'room_rent'        => $roomRent,
                'other_charge'     => $otherCharge,
                'grand_total'      => $grandTotal,
            ]
        ]);
    }

    public function store(Request $request)
    {
        // === 1. Validate input ===
        $validator = Validator::make($request->all(), [
            'month'          => 'required|string',
            'old_electric'   => 'required|numeric|min:0',
            'new_electric'   => 'required|numeric|min:0',
            'electric_rate'  => 'required|numeric|min:0',
            'old_water'      => 'required|numeric|min:0',
            'new_water'      => 'required|numeric|min:0',
            'water_rate'     => 'required|numeric|min:0',
            'other_charge'   => 'nullable|numeric|min:0',
        ], [
            'month.required'         => __('validation.required_month'),
            'old_electric.required'  => __('validation.required_old_electric'),
            'new_electric.required'  => __('validation.required_new_electric'),
            'electric_rate.required' => __('validation.required_electric_rate'),
            'old_water.required'     => __('validation.required_old_water'),
            'new_water.required'     => __('validation.required_new_water'),
            'water_rate.required'    => __('validation.required_water_rate'),
        ]);

        // === 2. Custom validation logic ===
        $validator->after(function ($validator) use ($request) {
            if ($request->new_electric < $request->old_electric) {
                $validator->errors()->add('new_electric', __('validation.new_electric_must_be_greater'));
            }
            if ($request->new_water < $request->old_water) {
                $validator->errors()->add('new_water', __('validation.new_water_must_be_greater'));
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // === 3. Build payload ===
        $payload = [
            'room_id'       => $request->room_id,
            'old_electric'  => (float) $request->old_electric,
            'new_electric'  => (float) $request->new_electric,
            'electric_rate' => (float) $request->electric_rate,
            'old_water'     => (float) $request->old_water,
            'new_water'     => (float) $request->new_water,
            'water_rate'    => (float) $request->water_rate,
            'other_charge'  => (float) ($request->other_charge ?? 0),
            'invoice_date'  => $request->month, // Y-m format
        ];

        // === 4. Call API ===
        $response = $this->api()->post('v1/invoices', $payload);

        // === 5. Handle API response ===
        if (!empty($response['success']) && $response['success'] === true) {
            return redirect()
                ->route('invoice.index')
                ->with('success', __('invoice.created_successfully'));
        }

        return redirect()->back()
            ->withInput()
            ->withErrors($response['errors'] ?? ['error' => $response['message'] ?? __('invoice.create_failed')]);
    }

    public function show(Request $request, $id)
    {
        try {
            // === 1. Fetch invoice details from API ===
            $response = $this->api()->get("v1/invoices/{$id}");

            // === 2. Validate and extract invoice data ===
            $invoice = $response['invoice'] ?? null;

            if (!$invoice) {
                return redirect()
                    ->route('invoice.index')
                    ->withErrors(['error' => __('invoice.not_found')]);
            }

            // === 3. Optional: compute useful totals (for view display) ===
            $invoice['electric_total'] = ($invoice['new_electric'] - $invoice['old_electric']) * $invoice['electric_rate'];
            $invoice['water_total']    = ($invoice['new_water'] - $invoice['old_water']) * $invoice['water_rate'];
            $invoice['grand_total']    = $invoice['total']
                ?? ($invoice['electric_total'] + $invoice['water_total'] + $invoice['room_fee'] + $invoice['other_charge']);

            // === 4. Pass to view ===
            return view('app.invoices.show', compact('invoice'));
        } catch (\Throwable $e) {
            // === 5. Handle unexpected errors gracefully ===
            Log::error('Failed to fetch invoice details', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.index')
                ->withErrors(['error' => __('invoice.fetch_failed')]);
        }
    }
}
