<?php

namespace App\Http\Controllers;

use App\Enum\Active;
use App\Enum\InvoiceStatus;
use App\Enum\RoomStatus;
use App\Utils\Util;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{

    public function index(Request $request, $locationId)
    {
        try {
            // === 1. Get filter/search params ===
            $statusFilter = $request->query('status');
            $search = $request->query('search');
            $locationFilter = $request->query('location');
            $buildingFilter = $request->query('building_name');
            $floorFilter = $request->query('floor_name');
            $roomFilter = $request->query('room_name');
            $roomTypeFilter = $request->query('room_type');
            $monthFilter = $request->query('month');
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');

            // === 2. Fetch invoices from API (by selected locationId) ===
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->get('v1/invoices');

            $invoices = $response['data'] ?? [];

            $totals = [
                'room_fee' => $response['total_room_fee'] ?? 0,
                'electric_charge' => $response['total_electric_charge'] ?? 0,
                'water_charge' => $response['total_water_charge'] ?? 0,
            ];

            // === 3. Apply Filters (same as you have) ===

            if ($statusFilter !== null && $statusFilter !== '') {
                $invoices = array_filter($invoices, fn($inv) => ($inv['status'] ?? null) == $statusFilter);
            }

            if ($locationFilter !== null && $locationFilter !== '') {
                $invoices = array_filter($invoices, fn($inv) => ($inv['room']['location_id'] ?? null) == $locationFilter);
            }

            if ($buildingFilter) {
                $invoices = array_filter(
                    $invoices,
                    fn($inv) =>
                    str_contains(strtolower($inv['room']['building_name'] ?? ''), strtolower($buildingFilter))
                );
            }

            if ($floorFilter) {
                $invoices = array_filter(
                    $invoices,
                    fn($inv) =>
                    str_contains(strtolower($inv['room']['floor_name'] ?? ''), strtolower($floorFilter))
                );
            }

            if ($roomFilter) {
                $invoices = array_filter(
                    $invoices,
                    fn($inv) =>
                    str_contains(strtolower($inv['room']['room_name'] ?? ''), strtolower($roomFilter))
                );
            }

            if ($roomTypeFilter !== null && $roomTypeFilter !== '') {
                $invoices = array_filter($invoices, fn($inv) => ($inv['room']['room_type_id'] ?? null) == $roomTypeFilter);
            }

            if ($monthFilter) {
                $invoices = array_filter(
                    $invoices,
                    fn($inv) =>
                    isset($inv['invoice_date']) && Carbon::parse($inv['invoice_date'])->format('Y-m') === $monthFilter
                );
            }

            if ($fromDate && $toDate) {
                $invoices = array_filter(
                    $invoices,
                    fn($inv) =>
                    isset($inv['invoice_date']) &&
                    Carbon::parse($inv['invoice_date'])->toDateString() >= $fromDate &&
                    Carbon::parse($inv['invoice_date'])->toDateString() <= $toDate
                );
            }

            if ($search) {
                $search = strtolower($search);
                $invoices = array_filter(
                    $invoices,
                    fn($inv) =>
                    str_contains(strtolower($inv['invoice_no'] ?? ''), $search) ||
                    str_contains(strtolower($inv['room']['room_name'] ?? ''), $search)
                );
            }

            // ✅ Re-index after filters (important for foreach index)
            $invoices = array_values($invoices);

            // === 4. Add computed fields like userIndex (NO Blade calculation) ===
            $invoices = array_map(function ($inv) {
                $oldE = (float) ($inv['old_electric'] ?? 0);
                $newE = (float) ($inv['new_electric'] ?? 0);
                $eRate = (float) ($inv['electric_rate'] ?? 0);

                $oldW = (float) ($inv['old_water'] ?? 0);
                $newW = (float) ($inv['new_water'] ?? 0);
                $wRate = (float) ($inv['water_rate'] ?? 0);

                $eUsed = max(0, $newE - $oldE);
                $wUsed = max(0, $newW - $oldW);

                $eTotal = $eUsed * $eRate;
                $wTotal = $wUsed * $wRate;

                $roomFee = (float) ($inv['room_fee'] ?? 0);
                $other = (float) ($inv['other_charge'] ?? 0);

                // If API gives "total" use it, else compute
                $grand = (float) ($inv['total'] ?? ($roomFee + $eTotal + $wTotal + $other));

                $inv['calc'] = [
                    'old_electric' => $oldE,
                    'new_electric' => $newE,
                    'electric_rate' => $eRate,
                    'electric_used' => $eUsed,
                    'electric_total' => $eTotal,

                    'old_water' => $oldW,
                    'new_water' => $newW,
                    'water_rate' => $wRate,
                    'water_used' => $wUsed,
                    'water_total' => $wTotal,

                    'room_fee' => $roomFee,
                    'other_charge' => $other,
                    'grand_total' => $grand,
                ];

                return $inv;
            }, $invoices);

            // === 5. Fetch dropdown data ===
            $locations = $this->api()->get('v1/locations')['locations']['data'] ?? [];
            $roomTypes = $this->api()->get('v1/room-types')['room_types']['data'] ?? [];
            $statuses = InvoiceStatus::all();

            // === 6. Return view ===
            return view('app.invoices.index', [
                'invoices' => $invoices,
                'totals' => $totals,
                'locationId' => $locationId,

                'filter_status' => $statusFilter,
                'filter_location' => $locationFilter,
                'filter_building' => $buildingFilter,
                'filter_floor' => $floorFilter,
                'filter_room' => $roomFilter,
                'filter_room_type' => $roomTypeFilter,
                'filter_month' => $monthFilter,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'search' => $search,

                'locations' => $locations,
                'roomTypes' => $roomTypes,
                'statuses' => $statuses,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch invoices for location', [
                'location_id' => $locationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.user_chooselocation')
                ->withErrors(['error' => __('invoice.fetch_failed')]);
        }
    }

    public function showLocation(Request $request)
    {
        // === 4. Fetch dropdown data ===
        $locations = $this->api()->get('v1/locations')['locations']['data'] ?? [];

        return view('app.invoices.show_location', compact('locations'));
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
                'Location-Id' => $locationId,
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
            ->withHeaders(['Location-Id' => $locationId])
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
            'month' => 'required|string',
            'old_electric' => 'required|numeric|min:0',
            'new_electric' => 'required|numeric|min:0',
            'electric_rate' => 'required|numeric|min:0',
            'old_water' => 'required|numeric|min:0',
            'new_water' => 'required|numeric|min:0',
            'water_rate' => 'required|numeric|min:0',
            'other_charge' => 'nullable|numeric|min:0',
        ], [
            // === 3. Custom message translations ===
            'month.required' => __('validation.required_month'),
            'old_electric.required' => __('validation.required_old_electric'),
            'new_electric.required' => __('validation.required_new_electric'),
            'electric_rate.required' => __('validation.required_electric_rate'),
            'old_water.required' => __('validation.required_old_water'),
            'new_water.required' => __('validation.required_new_water'),
            'water_rate.required' => __('validation.required_water_rate'),
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
            ->withHeaders(['Location-Id' => $locationId])
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
        $oldElectric = (float) str_replace(',', '', $request->old_electric);
        $newElectric = (float) str_replace(',', '', $request->new_electric);
        $electricRate = (float) str_replace(',', '', $request->electric_rate);
        $oldWater = (float) str_replace(',', '', $request->old_water);
        $newWater = (float) str_replace(',', '', $request->new_water);
        $waterRate = (float) str_replace(',', '', $request->water_rate);
        $otherCharge = (float) str_replace(',', '', $request->other_charge ?? 0);

        // === 5. Calculate usage ===
        $electricUsed = max(0, $newElectric - $oldElectric);
        $waterUsed = max(0, $newWater - $oldWater);

        // === 6. Calculate subtotals ===
        $electricTotal = $electricUsed * $electricRate;
        $waterTotal = $waterUsed * $waterRate;
        $roomRent = isset($room['room_type']['price']) ? (float) $room['room_type']['price'] : 0;

        // === 7. Grand total (match your calculateTotal method) ===
        $grandTotal = $roomRent + $electricTotal + $waterTotal + $otherCharge;

        // === 8. Return view with preview data ===
        return view('app.invoices.create', [
            'room' => $room,
            'clients' => $clients,
            'preview' => [
                'month' => $request->month,
                'old_electric' => $oldElectric,
                'new_electric' => $newElectric,
                'electric_rate' => $electricRate,
                'electric_usage' => $electricUsed,
                'electric_total' => $electricTotal,
                'old_water' => $oldWater,
                'new_water' => $newWater,
                'water_rate' => $waterRate,
                'water_usage' => $waterUsed,
                'water_total' => $waterTotal,
                'room_rent' => $roomRent,
                'other_charge' => $otherCharge,
                'grand_total' => $grandTotal,
            ]
        ]);
    }

    public function store(Request $request)
    {
        // === 1. Validate input ===
        $validator = Validator::make($request->all(), [
            'month' => 'required|string',
            'old_electric' => 'required|numeric|min:0',
            'new_electric' => 'required|numeric|min:0',
            'electric_rate' => 'required|numeric|min:0',
            'old_water' => 'required|numeric|min:0',
            'new_water' => 'required|numeric|min:0',
            'water_rate' => 'required|numeric|min:0',
            'other_charge' => 'nullable|numeric|min:0',
        ], [
            'month.required' => __('validation.required_month'),
            'old_electric.required' => __('validation.required_old_electric'),
            'new_electric.required' => __('validation.required_new_electric'),
            'electric_rate.required' => __('validation.required_electric_rate'),
            'old_water.required' => __('validation.required_old_water'),
            'new_water.required' => __('validation.required_new_water'),
            'water_rate.required' => __('validation.required_water_rate'),
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
            'room_id' => $request->room_id,
            'old_electric' => (float) $request->old_electric,
            'new_electric' => (float) $request->new_electric,
            'electric_rate' => (float) $request->electric_rate,
            'old_water' => (float) $request->old_water,
            'new_water' => (float) $request->new_water,
            'water_rate' => (float) $request->water_rate,
            'other_charge' => (float) ($request->other_charge ?? 0),
            'invoice_date' => $request->month, // Y-m format
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

    public function show(Request $request, $id, $locationId)
    {
        try {
            // === 1. Fetch invoice details from API ===
            $response = $this->api()->withHeaders(['Location-Id' => $locationId])->get("v1/invoices/{$id}");

            // === 2. Validate and extract invoice data ===
            $invoice = $response['invoice'] ?? null;

            if (!$invoice) {
                return redirect()
                    ->route('invoice.index')
                    ->withErrors(['error' => __('invoice.not_found')]);
            }

            // === 3. Optional: compute useful totals (for view display) ===
            $invoice['electric_total'] = ($invoice['new_electric'] - $invoice['old_electric']) * $invoice['electric_rate'];
            $invoice['water_total'] = ($invoice['new_water'] - $invoice['old_water']) * $invoice['water_rate'];
            $invoice['grand_total'] = $invoice['total']
                ?? ($invoice['electric_total'] + $invoice['water_total'] + $invoice['room_fee'] + $invoice['other_charge']);

            // === 4. Pass to view ===
            return view('app.invoices.show', compact('invoice', 'locationId'));
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

    public function edit(Request $request, $id, $locationId)
    {
        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->get("v1/invoices/{$id}");

            $invoice = $response['invoice'] ?? null;

            if (!$invoice) {
                return redirect()
                    ->route('invoice.user_index', $locationId)
                    ->withErrors(['error' => __('invoice.not_found')]);
            }

            $statuses = InvoiceStatus::all(); // ✅ add this

            return view('app.invoices.edit', compact('invoice', 'locationId', 'statuses'));
        } catch (\Throwable $e) {
            Log::error('Failed to fetch invoice details for editing', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.user_index', $locationId)
                ->withErrors(['error' => __('invoice.fetch_failed')]);
        }
    }

    public function update(Request $request, $id, $locationId)
    {
        $rules = [
            'month' => ['required', 'string'],
            'old_electric' => ['required', 'numeric', 'min:0'],
            'new_electric' => ['required', 'numeric', 'min:0'],
            'electric_rate' => ['required', 'numeric', 'min:0'],
            'old_water' => ['required', 'numeric', 'min:0'],
            'new_water' => ['required', 'numeric', 'min:0'],
            'water_rate' => ['required', 'numeric', 'min:0'],
            'other_charge' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(InvoiceStatus::all()))],
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if (!$this->normalizeInvoiceMonth($request->month)) {
                $validator->errors()->add('month', __('month មិនត្រូវតាមទ្រង់ទ្រាយ Y-m'));
            }

            if ((float) $request->new_electric < (float) $request->old_electric) {
                $validator->errors()->add('new_electric', __('validation.new_electric_must_be_greater'));
            }

            if ((float) $request->new_water < (float) $request->old_water) {
                $validator->errors()->add('new_water', __('validation.new_water_must_be_greater'));
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        $payload = [
            'invoice_date' => $this->normalizeInvoiceMonth($request->month),
            'old_electric' => (float) $request->old_electric,
            'new_electric' => (float) $request->new_electric,
            'electric_rate' => (float) $request->electric_rate,
            'old_water' => (float) $request->old_water,
            'new_water' => (float) $request->new_water,
            'water_rate' => (float) $request->water_rate,
            'other_charge' => (float) ($request->other_charge ?? 0),
            'status' => (int) $request->status,
        ];

        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $locationId])
                ->patch("v1/invoices/{$id}", $payload);
        } catch (RequestException $e) {
            $res = $e->response;

            Log::error('Invoice PATCH RequestException', [
                'invoice_id' => $id,
                'location_id' => $locationId,
                'payload' => $payload,
                'status' => $res?->status(),
                'body' => $res?->body(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'error' => $res?->json('message')
                        ?? $res?->body()
                        ?? __('invoice.update_failed'),
                ]);
        } catch (\Throwable $e) {
            Log::error('Invoice PATCH Throwable', [
                'invoice_id' => $id,
                'location_id' => $locationId,
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'error' => __('invoice.update_failed'),
                ]);
        }

        if (($response['success'] ?? false) === true || ($response['status'] ?? null) === 'success') {
            return redirect()
                ->back()
                ->with('success', __('invoice.updated_successfully'));
        }

        return redirect()
            ->back()
            ->withInput()
            ->withErrors(
                $response['errors']
                ?? ['error' => $response['message'] ?? __('invoice.update_failed')]
            );
    }

    private function normalizeInvoiceMonth(?string $month): ?string
    {
        if (blank($month)) {
            return null;
        }

        $month = trim($month);

        foreach (['Y-m', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, $month)
                    ->startOfMonth()
                    ->format('Y-m-d');
            } catch (\Throwable) {
                //
            }
        }

        try {
            return Carbon::parse($month)
                ->startOfMonth()
                ->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function destroy(Request $request, $id, $locationId)
    {
        try {
            // === 1. Call API to delete invoice ===
            $response = $this->api()->withHeaders(['Location-Id' => $locationId])->delete("v1/invoices/{$id}");

            // === 2. Handle API response ===
            if (!empty($response['success']) && $response['success'] === true) {
                return redirect()->back()->with('success', __('invoice.deleted_successfully'));
            }

            return redirect()
                ->route('invoice.index')
                ->withErrors($response['errors'] ?? ['error' => $response['message'] ?? __('invoice.delete_failed')]);
        } catch (\Throwable $e) {
            // === 3. Handle unexpected errors gracefully ===
            Log::error('Failed to delete invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.user_index', $locationId)
                ->withErrors(['error' => __('invoice.delete_failed') . ' - ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id, $locationId)
    {
        // ✅ Allowed statuses
        $allowedStatuses = array_keys(InvoiceStatus::all());

        // 1️⃣ Validate input (cast string to int first)
        $status = (int) $request->input('status');

        $validator = Validator::make(
            ['status' => $status],
            ['status' => ['required', 'integer', 'in:' . implode(',', $allowedStatuses)]],
            [
                'status.required' => __('validation.required', ['attribute' => __('invoice.status')]),
                'status.in' => __('validation.in', ['attribute' => __('invoice.status')]),
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // dd($status);
            // 2️⃣ Send to API as integer (PATCH)
            $response = $this->api()->withHeaders(['Location-Id' => $locationId])->patch("v1/invoices/{$id}/status", [
                '_method' => 'PATCH',
                'status' => $status
            ]);

            // 3️⃣ Handle API response
            if (!empty($response['success']) && $response['success'] === true) {
                return redirect()->back()
                    ->with('success', __('invoice.status_updated_successfully'));
            }

            return redirect()->back()
                ->withInput()
                ->withErrors($response['errors'] ?? ['error' => $response['message'] ?? __('invoice.update_failed')]);
        } catch (RequestException $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => __('invoice.update_failed') . ' - ' . $e->getMessage()]);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => __('invoice.update_failed') . ' - ' . $e->getMessage()]);
        }
    }

    public function userChooseLocation(Request $request)
    {
        $locations = collect(Session::get('user.user_locations', []))
            ->pluck('location')
            ->values()
            ->toArray();

        return view('app.invoices.user-choose-loation', compact('locations'));
    }

    // for user
    public function userCreateInvoice(Request $request, string $location)
    {
        try {
            $response = $this->api()
                ->withHeaders(['Location-Id' => $location])
                ->get('v1/rooms', $request->query());

            $rooms = data_get($response, 'rooms.data', []);

            // ✅ attach status meta for badge
            $rooms = array_map(function ($room) {
                $room['status_meta'] = RoomStatus::getStatus($room['status'] ?? null);
                return $room;
            }, $rooms);

            return view('app.invoices.user-create-invoice', [
                'rooms' => $rooms,
                'locationId' => $location,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch rooms', [
                'location_id' => $location,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.user_choose_location')
                ->withErrors(['error' => __('invoice.fetch_failed')]);
        }
    }

    /**
     * Store multiple invoices at once.
     */
    public function storeMultiple(Request $request, string $location)
    {
        // ✅ If your Blade changed to invoice_date[] (Y-m-d), use invoice_date.* here (NOT month.*)
        $request->validate([
            'room_id.*' => 'required|uuid',

            // ✅ date in Y-m-d
            'invoice_date.*' => 'required|date_format:Y-m-d',

            'old_electric.*' => 'required|numeric|min:0',
            'new_electric.*' => 'required|numeric|min:0',

            // ✅ global (fill once)
            'electric_rate' => 'required|numeric|min:0',
            'water_rate' => 'required|numeric|min:0',

            'old_water.*' => 'required|numeric|min:0',
            'new_water.*' => 'required|numeric|min:0',
            'other_charge.*' => 'nullable|numeric|min:0',
        ]);

        $count = count($request->room_id ?? []);
        $errors = [];
        $alertErrors = [];
        $successCount = 0;

        for ($i = 0; $i < $count; $i++) {

            $rowLabel = 'Row ' . ($i + 1);

            // ✅ avoid "undefined offset" if something missing
            $oldElectric = (float) ($request->old_electric[$i] ?? 0);
            $newElectric = (float) ($request->new_electric[$i] ?? 0);
            $oldWater = (float) ($request->old_water[$i] ?? 0);
            $newWater = (float) ($request->new_water[$i] ?? 0);

            if ($newElectric < $oldElectric) {
                $msg = __('validation.new_electric_must_be_greater');
                $errors["new_electric.$i"] = [$msg];
                $alertErrors[] = "$rowLabel: $msg";
                continue;
            }

            if ($newWater < $oldWater) {
                $msg = __('validation.new_water_must_be_greater');
                $errors["new_water.$i"] = [$msg];
                $alertErrors[] = "$rowLabel: $msg";
                continue;
            }

            $payload = [
                'room_id' => $request->room_id[$i],
                'old_electric' => $oldElectric,
                'new_electric' => $newElectric,

                // ✅ same for all rooms
                'electric_rate' => (float) $request->electric_rate,

                'old_water' => $oldWater,
                'new_water' => $newWater,

                // ✅ same for all rooms
                'water_rate' => (float) $request->water_rate,

                'other_charge' => (float) ($request->other_charge[$i] ?? 0),

                // ✅ match your UI field name
                'invoice_date' => $request->invoice_date[$i],
            ];

            try {
                $response = $this->api()->post(
                    'v1/invoices',
                    $payload,
                    token: null,
                    asForm: false,
                    files: [],
                    fileField: 'documents[]',
                    moreHeaders: ['Location-Id' => $location]
                );

                $isSuccess = !empty($response['success']) || (($response['status'] ?? null) === 'success');

                if ($isSuccess) {
                    $successCount++;
                    continue;
                }

                $msg = collect($response['errors'] ?? [])
                    ->flatten()
                    ->first()
                    ?? __('invoice.create_failed');

                $errors["api.$i"] = [$msg];
                $alertErrors[] = "$rowLabel: $msg";
            } catch (RequestException $e) {
                $data = $e->response?->json();

                $msg = collect($data['errors'] ?? [])
                    ->flatten()
                    ->first()
                    ?? __('invoice.create_failed');

                $errors["api.$i"] = [$msg];
                $alertErrors[] = "$rowLabel: $msg";
            }
        }

        // ✅ handle "no room selected"
        if ($count === 0) {
            return redirect()->back()
                ->with('error', __('Please select at least one room.'))
                ->withInput();
        }

        if ($successCount === $count) {
            return redirect()->back()->with('success', __('All invoices saved successfully.'));
        }

        if ($successCount > 0) {
            return redirect()->back()
                ->with('warning', __('Some invoices were saved, but others failed.'))
                ->with('alert_errors', $alertErrors)
                ->withErrors($errors)
                ->withInput();
        }

        $firstApiMsg = $alertErrors[0] ?? __('No invoices were saved.');

        return redirect()->back()
            ->with('error', $firstApiMsg)
            ->with('alert_errors', $alertErrors)
            ->withErrors($errors)
            ->withInput();
    }


    public function userIndexChooseLocation()
    {
        $locations = collect(Session::get('user.user_locations', []))
            ->pluck('location')
            ->values()
            ->toArray();

        return view('app.invoices.user-index-choose-loation', compact('locations'));
    }

    public function userIndex(Request $request, string $location)
    {
        try {
            $statusFilter = $request->query('status');
            $search = trim((string) $request->query('search', ''));
            $buildingFilter = trim((string) $request->query('building_name', ''));
            $floorFilter = trim((string) $request->query('floor_name', ''));
            $roomFilter = trim((string) $request->query('room_name', ''));
            $roomTypeFilter = $request->query('room_type');
            $monthFilter = $request->query('month');
            $fromDate = $request->query('from_date');
            $toDate = $request->query('to_date');

            $fromDate = $fromDate ? Carbon::parse($fromDate)->toDateString() : null;
            $toDate = $toDate ? Carbon::parse($toDate)->toDateString() : null;

            $response = $this->api()
                ->withHeaders(['Location-Id' => $location])
                ->get('v1/invoices');

            $invoices = $response['data'] ?? [];

            $invoices = array_filter($invoices, function ($invoice) use ($statusFilter, $buildingFilter, $floorFilter, $roomFilter, $roomTypeFilter, $monthFilter, $fromDate, $toDate, $search) {
                $room = $invoice['room'] ?? [];

                if ($statusFilter !== null && $statusFilter !== '') {
                    if ((string) ($invoice['status'] ?? '') !== (string) $statusFilter) {
                        return false;
                    }
                }

                if ($roomTypeFilter !== null && $roomTypeFilter !== '') {
                    if ((string) ($room['room_type_id'] ?? '') !== (string) $roomTypeFilter) {
                        return false;
                    }
                }

                if ($buildingFilter !== '') {
                    if (!str_contains(mb_strtolower($room['building_name'] ?? ''), mb_strtolower($buildingFilter))) {
                        return false;
                    }
                }

                if ($floorFilter !== '') {
                    if (!str_contains(mb_strtolower($room['floor_name'] ?? ''), mb_strtolower($floorFilter))) {
                        return false;
                    }
                }

                if ($roomFilter !== '') {
                    if (!str_contains(mb_strtolower($room['room_name'] ?? ''), mb_strtolower($roomFilter))) {
                        return false;
                    }
                }

                $invoiceDate = $invoice['invoice_date'] ?? null;

                if (!$invoiceDate) {
                    if ($monthFilter || $fromDate || $toDate) {
                        return false;
                    }
                } else {
                    $parsedInvoiceDate = Carbon::parse($invoiceDate);
                    $invoiceDateString = $parsedInvoiceDate->toDateString();

                    if ($monthFilter && $parsedInvoiceDate->format('Y-m') !== $monthFilter) {
                        return false;
                    }

                    if ($fromDate && $invoiceDateString < $fromDate) {
                        return false;
                    }

                    if ($toDate && $invoiceDateString > $toDate) {
                        return false;
                    }
                }

                if ($search !== '') {
                    $query = mb_strtolower($search);
                    $invoiceNo = mb_strtolower((string) ($invoice['invoice_no'] ?? ''));
                    $roomName = mb_strtolower((string) ($room['room_name'] ?? ''));

                    if (!str_contains($invoiceNo, $query) && !str_contains($roomName, $query)) {
                        return false;
                    }
                }

                return true;
            });

            $totals = [
                'room_fee' => 0,
                'electric_charge' => 0,
                'water_charge' => 0,
                'other_charge' => 0,
                'grand_total' => 0,
            ];

            $invoices = collect(array_values($invoices))
                ->map(function ($invoice, $index) use (&$totals) {
                    return $this->decorateInvoiceForIndex($invoice, $index, $totals);
                })
                ->values()
                ->toArray();

            $roomTypes = $this->api()->get('v1/room-types')['room_types']['data'] ?? [];
            $statuses = $this->invoiceStatuses();

            $summaryCards = [
                [
                    'label' => __('Invoices'),
                    'value' => count($invoices),
                    'icon' => 'receipt-2',
                    'class' => 'bg-primary text-primary-fg',
                    'subtext' => __('Filtered result'),
                ],
                [
                    'label' => __('invoice.room_rent'),
                    'value' => $this->moneyText($totals['room_fee'], 0),
                    'icon' => 'home-dollar',
                    'class' => 'bg-success-lt text-success',
                    'subtext' => __('invoice.totals'),
                ],
                [
                    'label' => __('invoice.electric_total'),
                    'value' => $this->moneyText($totals['electric_charge'], 0),
                    'icon' => 'bolt',
                    'class' => 'bg-warning-lt text-warning',
                    'subtext' => __('invoice.totals'),
                ],
                [
                    'label' => __('invoice.water_total'),
                    'value' => $this->moneyText($totals['water_charge'], 0),
                    'icon' => 'droplet',
                    'class' => 'bg-cyan-lt text-cyan',
                    'subtext' => __('invoice.totals'),
                ],
                [
                    'label' => __('invoice.total_amount'),
                    'value' => $this->moneyText($totals['grand_total'], 0),
                    'icon' => 'cash-banknote',
                    'class' => 'bg-danger-lt text-danger',
                    'subtext' => __('Grand total'),
                ],
            ];

            return view('app.invoices.user-index', [
                'invoices' => $invoices,
                'totals' => $totals,
                'summaryCards' => $summaryCards,
                'locationId' => $location,

                'filter_status' => $statusFilter,
                'filter_building' => $buildingFilter,
                'filter_floor' => $floorFilter,
                'filter_room' => $roomFilter,
                'filter_room_type' => $roomTypeFilter,
                'filter_month' => $monthFilter,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'search' => $search,

                'roomTypes' => $roomTypes,
                'statuses' => $statuses,
                'hasFilters' => filled($statusFilter)
                    || filled($buildingFilter)
                    || filled($floorFilter)
                    || filled($roomFilter)
                    || filled($roomTypeFilter)
                    || filled($monthFilter)
                    || filled($fromDate)
                    || filled($toDate)
                    || filled($search),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch invoices for location', [
                'location_id' => $location,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.user_chooselocation')
                ->withErrors(['error' => __('invoice.fetch_failed')]);
        }
    }

    private function decorateInvoiceForIndex(array $invoice, int $index, array &$totals): array
    {
        $oldElectric = (float) ($invoice['old_electric'] ?? 0);
        $newElectric = (float) ($invoice['new_electric'] ?? 0);
        $electricRate = (float) ($invoice['electric_rate'] ?? 0);

        $oldWater = (float) ($invoice['old_water'] ?? 0);
        $newWater = (float) ($invoice['new_water'] ?? 0);
        $waterRate = (float) ($invoice['water_rate'] ?? 0);

        $electricUsed = max(0, $newElectric - $oldElectric);
        $waterUsed = max(0, $newWater - $oldWater);

        $electricTotal = $electricUsed * $electricRate;
        $waterTotal = $waterUsed * $waterRate;

        $roomFee = (float) ($invoice['room_fee'] ?? 0);
        $otherCharge = (float) ($invoice['other_charge'] ?? 0);
        $grandTotal = (float) ($invoice['total'] ?? ($roomFee + $electricTotal + $waterTotal + $otherCharge));

        $totals['room_fee'] += $roomFee;
        $totals['electric_charge'] += $electricTotal;
        $totals['water_charge'] += $waterTotal;
        $totals['other_charge'] += $otherCharge;
        $totals['grand_total'] += $grandTotal;

        $statusMeta = InvoiceStatus::getStatus($invoice['status'] ?? null);

        $invoice['row_no'] = $index + 1;
        $invoice['collapse_id'] = 'invoice-detail-' . ($invoice['id'] ?? $index);
        $invoice['status_meta'] = $statusMeta;
        $invoice['invoice_date_text'] = $this->dateText($invoice['invoice_date'] ?? null);
        $invoice['due_date_text'] = $this->dateText($invoice['due_date'] ?? null);
        $invoice['created_at_text'] = $this->dateTimeText($invoice['created_at'] ?? null);
        $invoice['updated_at_text'] = $this->dateTimeText($invoice['updated_at'] ?? null);

        $invoice['room_name_text'] = data_get($invoice, 'room.room_name', '-');
        $invoice['building_text'] = data_get($invoice, 'room.building_name', '-');
        $invoice['floor_text'] = data_get($invoice, 'room.floor_name', '-');

        $invoice['calc'] = [
            'old_electric' => $oldElectric,
            'new_electric' => $newElectric,
            'electric_rate' => $electricRate,
            'electric_used' => $electricUsed,
            'electric_total' => $electricTotal,

            'old_water' => $oldWater,
            'new_water' => $newWater,
            'water_rate' => $waterRate,
            'water_used' => $waterUsed,
            'water_total' => $waterTotal,

            'room_fee' => $roomFee,
            'other_charge' => $otherCharge,
            'grand_total' => $grandTotal,
        ];

        $invoice['money'] = [
            'room_fee' => $this->moneyText($roomFee, 0),
            'electric_total' => $this->moneyText($electricTotal, 0),
            'water_total' => $this->moneyText($waterTotal, 0),
            'other_charge' => $this->moneyText($otherCharge, 0),
            'grand_total' => $this->moneyText($grandTotal, 0),
            'electric_rate' => $this->moneyText($electricRate, 2),
            'water_rate' => $this->moneyText($waterRate, 2),
        ];

        $invoice['urls'] = [
            'show' => !empty($invoice['id'])
                ? route('invoice.show', ['id' => $invoice['id'], 'locationId' => request()->route('location')])
                : null,
            'edit' => !empty($invoice['id'])
                ? route('invoice.edit', ['id' => $invoice['id'], 'locationId' => request()->route('location')])
                : null,
            'destroy' => !empty($invoice['id'])
                ? route('invoice.destroy', ['id' => $invoice['id'], 'locationId' => request()->route('location')])
                : null,
        ];

        return $invoice;
    }

    private function invoiceStatuses(): array
    {
        if (method_exists(InvoiceStatus::class, 'all')) {
            $values = array_keys(InvoiceStatus::all());
        } elseif (method_exists(InvoiceStatus::class, 'getAll')) {
            $values = array_keys(InvoiceStatus::getAll());
        } elseif (method_exists(InvoiceStatus::class, 'cases')) {
            $values = array_map(fn($case) => $case->value, InvoiceStatus::cases());
        } else {
            $values = collect((new \ReflectionClass(InvoiceStatus::class))->getConstants())
                ->filter(fn($value) => is_int($value) || is_string($value))
                ->values()
                ->toArray();
        }

        return collect($values)
            ->mapWithKeys(fn($value) => [$value => InvoiceStatus::getStatus($value)])
            ->toArray();
    }

    private function dateText(mixed $date): string
    {
        if (blank($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y');
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    private function dateTimeText(mixed $date): string
    {
        if (blank($date)) {
            return '-';
        }

        try {
            return Carbon::parse($date)->translatedFormat('d M Y H:i');
        } catch (\Throwable) {
            return (string) $date;
        }
    }

    private function moneyText(mixed $amount, int $decimals = 0): string
    {
        return number_format((float) ($amount ?? 0), $decimals, '.', ',') . '៛';
    }

    public function export(Request $request, $id, $locationId)
    {
        try {
            $response = $this->api()->get(
                "v1/invoices/{$id}",
                [],
                token: null,
                moreHeaders: ['Location-Id' => $locationId]
            );

            $invoice = $response['invoice'] ?? null;

            if (!$invoice) {
                return redirect()
                    ->route('invoice.index')
                    ->withErrors(['error' => __('invoice.not_found')]);
            }

            $invoice = $this->prepareInvoiceForExport($invoice);

            $defaultConfig = (new ConfigVariables())->getDefaults();
            $defaultFontConfig = (new FontVariables())->getDefaults();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_left' => 15,
                'margin_right' => 15,
                'fontDir' => array_merge(
                    $defaultConfig['fontDir'],
                    [public_path('fonts/')]
                ),
                'fontdata' => array_merge(
                    $defaultFontConfig['fontdata'],
                    [
                        'siemreap' => [
                            'R' => 'KhmerOS_battambang.ttf',
                            'B' => 'KhmerOS_battambang.ttf',
                            'useOTL' => 0xFF,
                            'useKashida' => 0,
                        ],
                    ]
                ),
                'default_font' => 'siemreap',
            ]);

            $html = view('app.invoices.export', compact('invoice', 'locationId'))->render();

            $mpdf->WriteHTML($html);

            return response()->streamDownload(
                function () use ($mpdf) {
                    echo $mpdf->Output('', 'S');
                },
                "invoice-{$invoice['invoice_no']}.pdf",
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Throwable $e) {
            Log::error('Failed to export invoice', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoice.index')
                ->withErrors(['error' => __('invoice.fetch_failed')]);
        }
    }

    public function exportMultiple(Request $request, $locationId)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->withErrors([
                'error' => __('Please select at least one invoice.'),
            ]);
        }

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_left' => 18,
            'margin_right' => 18,
            'fontDir' => array_merge(
                $defaultConfig['fontDir'],
                [public_path('fonts/')]
            ),
            'fontdata' => array_merge(
                $defaultFontConfig['fontdata'],
                [
                    'siemreap' => [
                        'R' => 'KhmerOS_battambang.ttf',
                        'B' => 'KhmerOS_battambang.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 0,
                    ],
                ]
            ),
            'default_font' => 'siemreap',
        ]);

        $written = 0;

        foreach ($ids as $id) {
            $response = $this->api()->get(
                "v1/invoices/{$id}",
                [],
                token: null,
                moreHeaders: ['Location-Id' => $locationId]
            );

            $invoice = $response['invoice'] ?? null;

            if (!$invoice) {
                continue;
            }

            $invoice = $this->prepareInvoiceForExport($invoice);

            if ($written > 0) {
                $mpdf->AddPage();
            }

            $mpdf->WriteHTML(
                view('app.invoices.export', compact('invoice', 'locationId'))->render()
            );

            $written++;
        }

        if ($written === 0) {
            return back()->withErrors([
                'error' => __('invoice.not_found'),
            ]);
        }

        return response()->streamDownload(
            fn() => print ($mpdf->Output('', 'S')),
            'invoices-' . now()->format('d-M-Y') . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function prepareInvoiceForExport(array $invoice): array
    {
        $oldElectric = (float) ($invoice['old_electric'] ?? 0);
        $newElectric = (float) ($invoice['new_electric'] ?? 0);
        $electricRate = (float) ($invoice['electric_rate'] ?? 0);

        $oldWater = (float) ($invoice['old_water'] ?? 0);
        $newWater = (float) ($invoice['new_water'] ?? 0);
        $waterRate = (float) ($invoice['water_rate'] ?? 0);

        $roomFee = (float) ($invoice['room_fee'] ?? 0);
        $otherCharge = (float) ($invoice['other_charge'] ?? 0);

        $invoice['electric_total'] = max(0, $newElectric - $oldElectric) * $electricRate;
        $invoice['water_total'] = max(0, $newWater - $oldWater) * $waterRate;

        $invoice['grand_total'] = $invoice['total']
            ?? ($invoice['electric_total'] + $invoice['water_total'] + $roomFee + $otherCharge);

        $client = $this->invoiceClient($invoice);

        $gender = $this->normalizeGender($client['gender'] ?? null);
        $genderText = $this->genderText($gender);

        $client['gender_mapped'] = $gender;
        $client['gender_text'] = $genderText;

        $invoice['client'] = $client;
        $invoice['client_gender'] = $gender;
        $invoice['client_gender_text'] = $genderText;
        $invoice['customer_gender_text'] = $genderText;

        if (isset($invoice['room']['clients']) && is_array($invoice['room']['clients'])) {
            foreach ($invoice['room']['clients'] as $index => $roomClient) {
                if (!is_array($roomClient)) {
                    continue;
                }

                if ((string) ($roomClient['id'] ?? '') === (string) ($invoice['client_id'] ?? '')) {
                    $invoice['room']['clients'][$index]['gender_mapped'] = $gender;
                    $invoice['room']['clients'][$index]['gender_text'] = $genderText;
                }
            }
        }

        return $invoice;
    }

    private function invoiceClient(array $invoice): array
    {
        if (!empty($invoice['client']) && is_array($invoice['client'])) {
            return $invoice['client'];
        }

        $clients = data_get($invoice, 'room.clients', []);

        if (!is_array($clients)) {
            return [];
        }

        $clientId = $invoice['client_id'] ?? null;

        if ($clientId) {
            $matchedClient = collect($clients)->first(function ($client) use ($clientId) {
                return is_array($client)
                    && (string) ($client['id'] ?? '') === (string) $clientId;
            });

            if ($matchedClient) {
                return $matchedClient;
            }
        }

        return collect($clients)->first(fn($client) => is_array($client)) ?: [];
    }

    private function normalizeGender(mixed $gender): ?string
    {
        $value = mb_strtolower(trim((string) $gender));

        return match ($value) {
            'm', 'male', 'ប្រុស', '1' => 'm',
            'f', 'female', 'ស្រី', '2' => 'f',
            default => null,
        };
    }

    private function genderText(?string $gender): string
    {
        return match ($gender) {
            'm' => 'ប្រុស',
            'f' => 'ស្រី',
            default => 'N/A',
        };
    }
}
