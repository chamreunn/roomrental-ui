<?php

namespace App\Http\Controllers;

use App\Enum\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Get user locations from session
        $userLocations = Session::get('user.user_locations', []);

        // Extract actual locations
        $locations = collect($userLocations)->map(fn($ul) => [
            'pivot_id' => $ul['id'], // pivot ID if needed
            'location_id' => $ul['location_id'],
            'location_name' => $ul['location']['location_name'] ?? 'មិនមានទីតាំង',
        ])->toArray();

        $roomTypes = collect($this->api()->get('v1/room-types')['room_types']['data'] ?? []);
        $roomStatuses = RoomStatus::all();

        // Initialize counts
        $statusCounts = collect($roomStatuses)->mapWithKeys(fn($_, $key) => [$key => 0])->toArray();
        $statusCounts['all'] = 0;

        $groupedRooms = [];
        $locationCounts = [];

        // Loop through each location
        foreach ($locations as $location) {
            $locationId = $location['location_id'];
            $locationName = $location['location_name'];

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

            foreach ($rooms as &$room) {
                $statusKey = $room['status'] ?? null;
                $statusInfo = RoomStatus::getStatus($statusKey);

                if (!$statusInfo) continue;

                $statusCounts[$statusKey]++;

                $roomTypeName = $roomTypes->firstWhere('id', $room['room_type_id'] ?? null)['type_name']
                    ?? 'មិនមានប្រភេទបន្ទប់';

                // ==========================
                // 🔍 Rental End Detection
                // ==========================
                $room['is_ending_soon'] = false;

                $roomClients = $room['clients'] ?? [];
                if (!empty($roomClients)) {
                    $latestClient = collect($roomClients)
                        ->sortByDesc('start_rental_date')
                        ->first();

                    if (!empty($latestClient['end_rental_date'])) {
                        $today = \Carbon\Carbon::today();
                        $endDate = \Carbon\Carbon::parse($latestClient['end_rental_date']);
                        $daysLeft = $today->diffInDays($endDate, false);

                        // Mark if rental is ending within 7 days
                        if ($daysLeft >= 0 && $daysLeft <= 7) {
                            $room['is_ending_soon'] = true;
                        }
                    }
                }

                // ==========================
                // Normal Room Info
                // ==========================
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
        $recentClients = $responseClient['data'] ?? [];

        $bookingsByDate = collect($recentClients)
            ->groupBy(fn($client) => \Carbon\Carbon::parse($client['start_rental_date'])->format('Y-m-d'))
            ->map->count()
            ->sortKeys();

        return view('users.dashboard', compact(
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
