<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Utils\Util;
use App\Enum\Active;
use App\Enum\Status;
use App\Enum\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

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
            Log::error('Room fetch failed: ' . $e->getMessage());
        }

        return view('app.rooms.room', compact('buttons', 'rooms', 'locationId', 'colors'));
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
        $apiResponse = $this->api()->withHeaders(['location_id' => $locationId])->post('v1/rooms', $payload);

        // ✅ Handle success
        if (($apiResponse['status'] ?? '') === 'success') {
            return redirect()
                ->route('room.index', $locationId)
                ->with('success', __('room.created_successfully'));
        }

        // ❌ Handle failure
        return back()
            ->withInput()
            ->with('error', $apiResponse['errors']['room_name'] ?? __('room.create_failed'));
    }

    public function edit(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.room_list', $locationId), // <-- go back to previous page
            ],
        ];

        $color = $request->query('color');

        // Get location detail
        $roomtypeResponse = $this->api()->get("v1/room-types");
        $roomtypes = $roomtypeResponse['room_types']['data'] ?? null;

        $roomResponse = $this->api()->withHeaders(['location_id' => $locationId])->get("v1/rooms/{$roomId}");
        $room = $roomResponse['room'];

        return view('app.rooms.edit', compact('buttons', 'locationId', 'roomtypes', 'room', 'color'));
    }

    public function update(Request $request, $roomId, $locationId)
    {
        // ✅ Validate request
        $validated = $request->validate([
            'building_name' => 'required|string|max:255',
            'floor_name'    => 'required|string|max:100',
            'room_name'     => 'required|string|max:100',
            'room_type_id'  => 'required|uuid',
            'description'   => 'nullable|string|max:500',
        ], [
            'building_name.required' => __('room.building_name_required'),
            'building_name.string'   => __('room.building_name_string'),
            'building_name.max'      => __('room.building_name_max'),

            'floor_name.required'    => __('room.floor_name_required'),
            'floor_name.string'      => __('room.floor_name_string'),
            'floor_name.max'         => __('room.floor_name_max'),

            'room_name.required'     => __('room.name_required'),
            'room_name.string'       => __('room.name_string'),
            'room_name.max'          => __('room.name_max'),

            'room_type_id.required'  => __('roomtype.select_required'),
            'room_type_id.uuid'      => __('roomtype.select_invalid'),

            'description.string'     => __('room.description_string'),
            'description.max'        => __('room.description_max'),
        ]);

        // ✅ Prepare payload for API
        $payload = [
            '_method'       => 'PATCH', // If your API expects PATCH
            'building_name' => $validated['building_name'],
            'floor_name'    => $validated['floor_name'],
            'room_name'     => $validated['room_name'],
            'room_type_id'  => $validated['room_type_id'],
            'description'   => $validated['description'] ?? null,
            'updated_by'    => Session::get('user')['id'] ?? null,
        ];

        try {
            // ✅ Send to API (assuming your helper $this->api() is a wrapper for HTTP client)
            $apiResponse = $this->api()->withHeaders(['location_id' => $locationId])->post("v1/rooms/{$roomId}", $payload);

            if (($apiResponse['status'] ?? '') === 'success') {
                return redirect()->back()->with('success', __('room.updated_successfully'));
            }

            // ❌ API returned failure
            return back()
                ->withInput()
                ->withErrors($apiResponse['errors'] ?? [
                    'error' => $apiResponse['message'] ?? __('room.update_failed')
                ]);
        } catch (Exception $e) {
            // ❌ Handle network or other exceptions
            return back()
                ->withInput()
                ->withErrors(['error' => __('room.update_failed')]);
        }
    }

    public function show(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.room_list', $locationId),
            ],
        ];

        // ✅ Fetch room with invoices + clients
        $roomResponse = $this->api()
            ->withHeaders(['location_id' => $locationId])
            ->get("v1/rooms/{$roomId}");

        $room = $roomResponse['room'] ?? null;

        if (!$room) {
            abort(404, __('room.not_found'));
        }

        // ✅ Get room status
        $roomstatus = RoomStatus::getStatus($room['status']);


        // ✅ Prepare clients safely
        $clients = collect($room['clients'] ?? [])->map(function ($client) {
            $client['clientstatus'] = Active::getStatus($client['status']);
            $client['image'] =  apiBaseUrl() . $client['client_image'] ?? asset('images/default-avatar.png');

            // ✅ Keep raw dates
            $dobRaw = $client['date_of_birth'] ?? null;
            $startRentalRaw = $client['start_rental_date'] ?? null;
            $endRentalRaw = $client['end_rental_date'] ?? null;

            // ✅ Days left — use raw date (not translated)
            if (!empty($endRentalRaw)) {
                try {
                    $endDate = Carbon::parse($endRentalRaw)->startOfDay();
                    $daysLeft = now()->startOfDay()->diffInDays($endDate, false);
                } catch (Exception $e) {
                    $daysLeft = null; // handle invalid or localized date safely
                }
            } else {
                $daysLeft = null;
            }

            // ✅ Display-friendly formatted dates
            $client['dateOfBirth'] = $dobRaw
                ? Carbon::parse($dobRaw)->translatedFormat('d F Y')
                : __('N/A');

            $client['start_rental_date'] = $startRentalRaw
                ? Carbon::parse($startRentalRaw)->translatedFormat('d F Y')
                : __('N/A');

            $client['end_rental_date'] = $endRentalRaw
                ? Carbon::parse($endRentalRaw)->translatedFormat('d F Y')
                : __('N/A');

            // ✅ Rental status logic
            $client['days_left'] = $daysLeft;
            $client['nearly_end'] = false;
            $client['dot_color'] = 'bg-success';

            if (!is_null($daysLeft)) {
                if ($daysLeft < 0) {
                    $client['nearly_end'] = true;
                    $client['dot_color'] = 'bg-danger';
                    $client['alert_message'] = __('room.rental_expired');
                } elseif ($daysLeft <= 7) {
                    $client['nearly_end'] = true;
                    $client['dot_color'] = 'bg-warning';
                    $client['alert_message'] = trans_choice('room.rental_ending', $daysLeft, ['days' => $daysLeft]);
                } else {
                    $client['alert_message'] = __('room.rental_active');
                }
            } else {
                $client['alert_message'] = __('room.rental_ongoing');
            }

            return $client;
        });

        // dd($clients);

        // ✅ Get statuses
        $statuses = RoomStatus::all();
        $inactive = Active::INACTIVE;

        return view('app.rooms.show', compact(
            'room',
            'buttons',
            'roomstatus',
            'statuses',
            'clients',
            'inactive'
        ));
    }

    public function destroy($id, $locationId)
    {
        try {
            // ✅ Call API DELETE endpoint
            $apiResponse = $this->api()->withHeaders(['location_id' => $locationId])->delete("v1/rooms/{$id}");

            // ✅ Handle success
            if (($apiResponse['status'] ?? '') === 'success') {
                return redirect()->back()->with('success', __('room.deleted_successfully'));
            }

            // ❌ Handle failure
            return back()->withErrors([
                'error' => $apiResponse['errors'] ?? $apiResponse['message'] ?? __('room.delete_failed'),
            ]);
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage() ?: __('room.delete_failed'),
            ]);
        }
    }

    public function multiDestroy(Request $request, $locationId)
    {
        $roomIds = explode(',', $request->room_ids);

        try {
            foreach ($roomIds as $id) {
                $this->api()->withHeaders(['location_id' => $locationId])->delete("v1/rooms/{$id}");
            }

            return back()->with('success', __('room.deleted_successfully'));
        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage() ?: __('room.delete_failed'),
            ]);
        }
    }

    // for booking
    public function booking(Request $request, $roomId, $locationId)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('room.room_list', $locationId),
            ],
        ];

        $roomResponse = $this->api()->withHeaders(['location_id' => $locationId])->get("v1/rooms/{$roomId}");
        $room = $roomResponse['room'];

        $roomstatus = RoomStatus::getStatus($room['status']);

        return view('app.rooms.booking', compact('room', 'buttons', 'roomstatus'));
    }

    public function updateStatus(Request $request, $roomId, $locationId)
    {
        // ✅ Validate the incoming status value
        $validated = $request->validate([
            'status' => 'required|in:0,1,2,3',
        ], [
            'status.required' => __('room.status_required'),
            'status.in' => __('room.invalid_status'),
        ]);

        // ✅ Prepare payload for API
        $payload = [
            '_method'    => 'PATCH',
            'updated_by' => session('user.id') ?? null,
            'status'     => $validated['status'],
        ];

        try {
            // ✅ Send PATCH request to your API
            $apiResponse = $this->api()
                ->withHeaders(['location_id' => $locationId])
                ->post("v1/rooms/{$roomId}/status", $payload);

            // ✅ Check for success flag
            if (($apiResponse['status'] ?? '') === 'success' || ($apiResponse['success'] ?? false)) {
                return redirect()
                    ->back()
                    ->with('success', __('room.updated_successfully'));
            }

            // ❌ API returned an error response
            return back()
                ->withInput()
                ->withErrors([
                    'error' => $apiResponse['message'] ?? __('room.update_failed'),
                ]);
        } catch (\Throwable $e) {
            // ❌ Catch network, connection, or unexpected exceptions
            report($e); // optional: log it for debugging

            return back()
                ->withInput()
                ->withErrors(['error' => __('room.update_failed')]);
        }
    }
}
