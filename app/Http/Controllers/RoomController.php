<?php

namespace App\Http\Controllers;

use App\Enum\RoomStatus;
use App\Enum\Status;
use App\Utils\Util;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.rooms.index', compact('locations'));
    }

    public function rooms(Request $request, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.index'),
            ],
        ];

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
            Log::error('Room fetch failed: ' . $e->getMessage());
        }

        return view('app.rooms.room', compact('buttons', 'rooms'));
    }


    public function location(Request $request)
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.rooms.choose_location', compact('locations'));
    }

    public function create(Request $request, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.choose_location'),
            ],
        ];

        // Get location detail
        $roomtypeResponse = $this->api()->get("v1/room-types");
        $roomtypes = $roomtypeResponse['room_types']['data'] ?? null;

        return view('app.rooms.create', compact('buttons', 'locationId', 'roomtypes'));
    }

    public function store(Request $request, $locationId)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'building_name' => 'required|string|max:255',
            'floor_name'    => 'required|string|max:255',
            'room_name'     => 'required|string|max:255',
            'room_type_id'  => 'required|uuid',
            'description'   => 'nullable|string|max:1000',
        ]);

        // ✅ Prepare payload
        $payload = [
            'building_name' => $validated['building_name'],
            'floor_name'    => $validated['floor_name'],
            'room_name'     => $validated['room_name'],
            'room_type_id'  => $validated['room_type_id'],
            'description'   => $validated['description'] ?? null,
            'created_by'    => Session::get('user')['id'] ?? null,
            'updated_by'    => Session::get('user')['id'] ?? null,
        ];

        // ✅ Send API request with locationId in header
        $apiResponse = $this->api()->withHeaders(['locationId' => $locationId])->post('v1/rooms', $payload);

        // ✅ Handle success
        if (($apiResponse['status'] ?? '') === 'success') {
            return redirect()
                ->route('room.index', $locationId)
                ->with('success', __('room.created_successfully'));
        }

        // ❌ Handle failure
        return back()
            ->withInput()
            ->withErrors($apiResponse['errors'] ?? [
                'error' => $apiResponse['message'] ?? __('room.create_failed'),
            ]);
    }
}
