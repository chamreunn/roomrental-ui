<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Utils\Util;
use App\Enum\Active;
use App\Enum\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceController extends Controller
{
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

        return view('app.invoices.create', compact('room', 'clients'));
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
}
