<?php

namespace App\Http\Controllers;

use App\Enum\Active;
use App\Enum\RoomStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ManagerController extends Controller
{
   public function index(Request $request)
    {
        // ===============================
        // 1. USER LOCATIONS
        // ===============================
        $userLocations = Session::get('user.user_locations', []);

        $locations = collect($userLocations)->map(fn($ul) => [
            'location_id'   => $ul['location_id'],
            'location_name' => $ul['location']['location_name'] ?? 'មិនមានទីតាំង',
        ])->values();

        // ===============================
        // 2. FILTER INPUTS
        // ===============================
        $selectedLocationId = $request->get('location_id');
        $roomTypeId         = $request->get('room_type_id');
        $status             = $request->get('status');
        $search             = $request->get('search');

        // ===============================
        // 3. STATIC DATA
        // ===============================
        $roomTypes    = collect($this->api()->get('v1/room-types')['room_types']['data'] ?? []);
        $roomStatuses = RoomStatus::all();

        $groupedRooms   = [];
        $locationCounts = [];
        $statusCounts   = collect($roomStatuses)->mapWithKeys(fn($_, $key) => [$key => 0])->toArray();
        $statusCounts['all'] = 0;

        // ===============================
        // 4. FILTER LOCATIONS
        // ===============================
        $filteredLocations = $selectedLocationId
            ? $locations->where('location_id', $selectedLocationId)
            : $locations;

        // ===============================
        // 5. FETCH ROOMS BY LOCATION
        // ===============================
        foreach ($filteredLocations as $location) {
            $locationId   = $location['location_id'];
            $locationName = $location['location_name'];

            $query = array_filter([
                'room_type_id' => $roomTypeId,
                'status'       => $status,
                'search'       => $search,
            ]);

            $rooms = $this->api()->get(
                'v1/rooms',
                $query,
                null,
                ['Location-Id' => $locationId]
            )['rooms']['data'] ?? [];

            $locationCounts[$locationName] = count($rooms);
            $statusCounts['all'] += count($rooms);

            foreach ($rooms as $room) {
                $statusKey  = $room['status'] ?? null;
                $statusInfo = RoomStatus::getStatus($statusKey);
                if (!$statusInfo) continue;

                $statusCounts[$statusKey]++;

                $roomTypeName = $roomTypes
                    ->firstWhere('id', $room['room_type_id'])['type_name']
                    ?? 'មិនមានប្រភេទបន្ទប់';

                // ===============================
                // RENTAL END CHECK
                // ===============================
                $room['is_ending_soon'] = false;
                if (!empty($room['clients'])) {
                    $latestClient = collect($room['clients'])
                        ->sortByDesc('start_rental_date')
                        ->first();

                    if (!empty($latestClient['end_rental_date'])) {
                        $daysLeft = now()->diffInDays(
                            \Carbon\Carbon::parse($latestClient['end_rental_date']),
                            false
                        );

                        if ($daysLeft >= 0 && $daysLeft <= 7) {
                            $room['is_ending_soon'] = true;
                        }
                    }
                }

                // ===============================
                // UI HELPERS
                // ===============================
                $room['status_name']   = $statusInfo['name'];
                $room['status_class']  = $statusInfo['badge'];
                $room['status_text']   = $statusInfo['text'];
                $room['room_type_name'] = $roomTypeName;

                $groupedRooms[$locationName][$roomTypeName][$statusKey][] = $room;
            }
        }

        return view('manager.dashboard', [
            'groupedRooms' => $groupedRooms,
            'locations'    => $locations,
            'roomTypes'    => $roomTypes,
            'roomStatuses' => $roomStatuses,
            'locationCounts' => $locationCounts,
        ]);
    }
}
