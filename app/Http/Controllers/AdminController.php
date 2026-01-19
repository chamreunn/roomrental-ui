<?php

namespace App\Http\Controllers;

use App\Enum\Active;
use App\Enum\RoomStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // ===============================
        // 1. Fetch base data
        // ===============================
        $locations = $this->api()->get('v1/locations')['locations']['data'] ?? [];

        $roomTypes = collect(
            $this->api()->get('v1/room-types')['room_types']['data'] ?? []
        );

        $roomStatuses = RoomStatus::all();

        // ===============================
        // 2. Initialize containers
        // ===============================
        $groupedRooms = [];
        $locationCounts = [];
        $clientsByLocation = [];
        $recentClients = collect();

        $statusCounts = collect($roomStatuses)
            ->mapWithKeys(fn($_, $key) => [$key => 0])
            ->toArray();

        $statusCounts['all'] = 0;

        // ===============================
        // 3. Loop locations → rooms
        // ===============================
        foreach ($locations as $location) {

            $locationId = $location['id'];
            $locationName = $location['location_name'] ?? __('Unknown Location');

            // Filters
            $query = $request->only(['room_type_id', 'status', 'search']);
            $query['location_id'] = $locationId;

            // Fetch rooms per location
            $rooms = $this->api()->get(
                'v1/rooms',
                $query,
                token: null,
                moreHeaders: ['Location-Id' => $locationId]
            )['rooms']['data'] ?? [];

            $locationCounts[$locationName] = count($rooms);
            $statusCounts['all'] += count($rooms);

            foreach ($rooms as $room) {

                // ===============================
                // Room status info
                // ===============================
                $statusKey = $room['status'] ?? null;
                $statusInfo = RoomStatus::getStatus($statusKey);

                if (!$statusInfo) {
                    continue;
                }

                $statusCounts[$statusKey]++;

                $roomTypeName = $roomTypes
                    ->firstWhere('id', $room['room_type_id'] ?? null)['type_name']
                    ?? __('Unknown Type');

                // ===============================
                // Rental ending detection
                // ===============================
                $room['is_ending_soon'] = false;

                $roomClients = $room['clients'] ?? [];

                if (!empty($roomClients)) {
                    $latestClient = collect($roomClients)
                        ->sortByDesc('start_rental_date')
                        ->first();

                    if (!empty($latestClient['end_rental_date'])) {
                        try {
                            $daysLeft = now()->diffInDays(
                                Carbon::parse($latestClient['end_rental_date']),
                                false
                            );

                            if ($daysLeft >= 0 && $daysLeft <= 7) {
                                $room['is_ending_soon'] = true;
                            }
                        } catch (\Throwable $e) {
                            $room['is_ending_soon'] = false;
                        }
                    }
                }

                // ===============================
                // Decorate room
                // ===============================
                $room['status_name']  = $statusInfo['name'];
                $room['status_class'] = $statusInfo['badge'];
                $room['status_text']  = $statusInfo['text'];
                $room['room_type_name'] = $roomTypeName;

                // ===============================
                // Group rooms
                // ===============================
                $groupedRooms[$locationName][$roomTypeName][$statusKey][] = $room;

                // ===============================
                // Extract clients per location
                // ===============================
                foreach ($roomClients as $client) {

                    $client['room'] = [
                        'room_name'     => $room['room_name'] ?? '',
                        'building_name' => $room['building_name'] ?? '',
                        'floor_name'    => $room['floor_name'] ?? '',
                    ];

                    $client['location_name'] = $locationName;
                    $client['clientstatus'] = Active::getStatus($client['status']);
                    $client['image'] = api_image($client['client_image']);

                    $clientsByLocation[$locationName][] = $client;
                    $recentClients->push($client);
                }
            }
        }

        // ===============================
        // 4. Recent clients + stats
        // ===============================
        $recentClients = $recentClients
            ->sortByDesc('start_rental_date')
            ->take(10)
            ->values();

        $bookingsByDate = $recentClients
            ->groupBy(
                fn($c) =>
                Carbon::parse($c['start_rental_date'])->format('Y-m-d')
            )
            ->map->count();

        $hasRooms = !empty($groupedRooms);

        // ===============================
        // 5. Return view
        // ===============================
        return view('admin.dashboard', [
            'groupedRooms'      => $groupedRooms,
            'hasRooms'          => $hasRooms,
            'statusCounts'      => $statusCounts,
            'roomTypes'         => $roomTypes,
            'locations'         => $locations,
            'locationCounts'    => $locationCounts,
            'roomStatuses'      => $roomStatuses,
            'clientsByLocation' => $clientsByLocation,
            'recentClients'     => $recentClients,
            'bookingsByDate'    => $bookingsByDate,
        ]);
    }
}
