<?php

namespace App\Http\Controllers;

use App\Enum\RoomStatus;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Fetch locations, room types, and statuses
        $locations = $this->api()->get('v1/locations')['locations']['data'] ?? [];
        $roomTypes = collect($this->api->get('v1/room-types')['room_types']['data'] ?? []);
        $roomStatuses = RoomStatus::all();

        // Initialize counts
        $statusCounts = collect($roomStatuses)->mapWithKeys(fn($_, $key) => [$key => 0])->toArray();
        $statusCounts['all'] = 0;

        $groupedRooms = [];
        $locationCounts = [];

        // Loop through locations to get rooms
        foreach ($locations as $location) {
            $locationId = $location['id'];
            $locationName = $location['location_name'] ?? 'មិនមានទីតាំង';

            // Build filter query
            $query = $request->only(['room_type_id', 'status', 'search']);
            $query['location_id'] = $locationId;

            // Fetch rooms for this location
            $rooms = $this->api
                ->withHeaders(['location_id' => $locationId])
                ->get('v1/rooms', $query)['rooms']['data'] ?? [];

            // Count per location
            $locationCounts[$locationName] = count($rooms);
            $statusCounts['all'] += count($rooms);

            foreach ($rooms as $room) {
                $statusKey = $room['status'] ?? null;
                $statusInfo = RoomStatus::getStatus($statusKey);

                // Skip if invalid status
                if (!$statusInfo) continue;

                $statusCounts[$statusKey]++;

                $roomTypeName = $roomTypes->firstWhere('id', $room['room_type_id'] ?? null)['type_name']
                    ?? 'មិនមានប្រភេទបន្ទប់';

                // Add formatted info
                $room['status_name'] = $statusInfo['name'];
                $room['status_class'] = $statusInfo['badge'];
                $room['status_text'] = $statusInfo['text'];
                $room['room_type_name'] = $roomTypeName;

                // Group by location > room type > status
                $groupedRooms[$locationName][$roomTypeName][$statusKey][] = $room;
            }
        }

        $hasRooms = !empty($groupedRooms);

        $responseClient = $this->api()->get('v1/clients')['clients'] ?? [];

        // Extract the 'data' array from the paginated response
        $recentClients = $responseClient['data'] ?? [];

        // ✅ Count bookings per day (e.g., by start_rental_date)
        $bookingsByDate = collect($recentClients)
            ->groupBy(fn($client) => \Carbon\Carbon::parse($client['start_rental_date'])->format('Y-m-d'))
            ->map->count()
            ->sortKeys();

            // dd($groupedRooms);

        // Pass to view
        return view('admin.dashboard', compact(
            'groupedRooms',
            'hasRooms',
            'statusCounts',
            'roomTypes',
            'locations',
            'locationCounts',
            'roomStatuses',
            'recentClients',
            'bookingsByDate',
        ));
    }
}
