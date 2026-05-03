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
        $today = now()->startOfDay();

        $rentAlertDays = max(1, min(60, (int) $request->input('rent_alert_days', 7)));
        $invoiceAlertDays = 7;

        $nextInvoiceDate = $this->nextInvoiceDate($today);
        $invoiceWindowStart = $nextInvoiceDate->copy()->subDays($invoiceAlertDays);

        $isInvoiceWindow = $today->greaterThanOrEqualTo($invoiceWindowStart)
            && $today->lessThanOrEqualTo($nextInvoiceDate);

        $userLocations = Session::get('user.user_locations', []);

        $locations = collect($userLocations)
            ->map(function ($userLocation) {
                return [
                    'id' => $userLocation['location_id'] ?? data_get($userLocation, 'location.id'),
                    'location_id' => $userLocation['location_id'] ?? data_get($userLocation, 'location.id'),
                    'location_name' => data_get($userLocation, 'location.location_name', __('Unknown Location')),
                ];
            })
            ->filter(fn($location) => !empty($location['location_id']))
            ->values();

        $roomTypes = collect(
            $this->api()->get('v1/room-types')['room_types']['data'] ?? []
        );

        $roomStatuses = RoomStatus::all();

        $selectedLocationId = $request->input('location_id');

        $filteredLocations = $selectedLocationId
            ? $locations->where('location_id', $selectedLocationId)->values()
            : $locations;

        $roomSections = [];
        $locationCounts = [];
        $clientsByLocation = [];
        $recentClients = collect();
        $endingSoonTenants = collect();
        $upcomingInvoiceTenants = collect();

        $statusCounts = collect($roomStatuses)
            ->mapWithKeys(fn($_status, $key) => [$key => 0])
            ->toArray();

        $statusCounts['all'] = 0;

        foreach ($filteredLocations as $location) {
            $locationId = $location['location_id'];
            $locationName = $location['location_name'];

            $query = array_filter([
                'room_type_id' => $request->input('room_type_id'),
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ], fn($value) => $value !== null && $value !== '');

            $rooms = $this->api()->get(
                'v1/rooms',
                $query,
                null,
                ['Location-Id' => $locationId]
            )['rooms']['data'] ?? [];

            $locationCounts[$locationName] = count($rooms);
            $statusCounts['all'] += count($rooms);

            foreach ($rooms as $room) {
                $statusKey = $room['status'] ?? null;
                $statusInfo = RoomStatus::getStatus($statusKey);

                if (!$statusInfo) {
                    continue;
                }

                $statusCounts[$statusKey] = ($statusCounts[$statusKey] ?? 0) + 1;

                $roomType = $roomTypes->firstWhere('id', $room['room_type_id'] ?? null);

                $roomTypeName = $roomType['type_name']
                    ?? data_get($room, 'room_type.type_name')
                    ?? __('Unknown Type');

                $roomSize = $roomType['room_size']
                    ?? data_get($room, 'room_type.room_size')
                    ?? '-';

                $roomPrice = $roomType['price']
                    ?? data_get($room, 'room_type.price')
                    ?? null;

                $decoratedRoom = $this->decorateRoom(
                    room: $room,
                    locationId: $locationId,
                    roomTypeName: $roomTypeName,
                    roomSize: $roomSize,
                    roomPrice: $roomPrice,
                    statusInfo: $statusInfo
                );

                $roomEndingAlerts = collect();

                foreach (($room['clients'] ?? []) as $client) {
                    $decoratedClient = $this->decorateClient(
                        client: $client,
                        room: $decoratedRoom,
                        locationName: $locationName
                    );

                    $clientsByLocation[$locationName][] = $decoratedClient;
                    $recentClients->push($decoratedClient);

                    $clientStartDate = $this->parseDate($client['start_rental_date'] ?? null);
                    $clientEndDate = $this->parseDate($client['end_rental_date'] ?? null);
                    $isActiveClient = (string) ($client['status'] ?? '') === (string) Active::ACTIVE;

                    if ($isActiveClient && $clientEndDate) {
                        $daysLeft = $today->diffInDays($clientEndDate, false);

                        if ($daysLeft >= 0 && $daysLeft <= $rentAlertDays) {
                            $alertClient = $decoratedClient;
                            $alertClient['rent_ends_at'] = $clientEndDate->toDateString();
                            $alertClient['rent_ends_at_text'] = $this->formatDate($clientEndDate);
                            $alertClient['rent_days_left'] = $daysLeft;
                            $alertClient['rent_days_left_text'] = $daysLeft === 0
                                ? __('Today')
                                : trans_choice(':count day|:count days', $daysLeft, ['count' => $daysLeft]);

                            $endingSoonTenants->push($alertClient);
                            $roomEndingAlerts->push($alertClient);
                        }
                    }

                    if (
                        $isActiveClient
                        && $isInvoiceWindow
                        && $clientStartDate
                        && $clientStartDate->lessThanOrEqualTo($nextInvoiceDate)
                        && (!$clientEndDate || $clientEndDate->greaterThanOrEqualTo($nextInvoiceDate))
                    ) {
                        $invoiceClient = $decoratedClient;
                        $invoiceClient['next_invoice_date'] = $nextInvoiceDate->toDateString();
                        $invoiceClient['next_invoice_date_text'] = $this->formatDate($nextInvoiceDate);
                        $invoiceClient['invoice_days_left'] = $today->diffInDays($nextInvoiceDate, false);
                        $invoiceClient['invoice_days_left_text'] = $invoiceClient['invoice_days_left'] === 0
                            ? __('Today')
                            : trans_choice(':count day|:count days', $invoiceClient['invoice_days_left'], [
                                'count' => $invoiceClient['invoice_days_left'],
                            ]);
                        $invoiceClient['invoice_price'] = $roomPrice;
                        $invoiceClient['invoice_price_text'] = $this->formatMoney($roomPrice);

                        $upcomingInvoiceTenants->push($invoiceClient);
                    }
                }

                if ($roomEndingAlerts->isNotEmpty()) {
                    $nearestEndingTenant = $roomEndingAlerts
                        ->sortBy('rent_days_left')
                        ->first();

                    $decoratedRoom['is_ending_soon'] = true;
                    $decoratedRoom['ending_tenant_name'] = $nearestEndingTenant['username'];
                    $decoratedRoom['ending_tenant_days_text'] = $nearestEndingTenant['rent_days_left_text'];
                    $decoratedRoom['ending_tenant_url'] = $nearestEndingTenant['client_detail_url'] ?? null;
                }

                if (!isset($roomSections[$locationName])) {
                    $roomSections[$locationName] = [
                        'location_name' => $locationName,
                        'room_count' => 0,
                        'types' => [],
                    ];
                }

                if (!isset($roomSections[$locationName]['types'][$roomTypeName])) {
                    $roomSections[$locationName]['types'][$roomTypeName] = [
                        'room_type_name' => $roomTypeName,
                        'room_count' => 0,
                        'rooms' => [],
                    ];
                }

                $roomSections[$locationName]['room_count']++;
                $roomSections[$locationName]['types'][$roomTypeName]['room_count']++;
                $roomSections[$locationName]['types'][$roomTypeName]['rooms'][] = $decoratedRoom;
            }
        }

        $recentClients = $recentClients
            ->sortByDesc('start_rental_date')
            ->take(10)
            ->values();

        $bookingsByDate = $recentClients
            ->filter(fn($client) => !empty($client['start_rental_date']))
            ->groupBy(fn($client) => Carbon::parse($client['start_rental_date'])->format('Y-m-d'))
            ->map->count();

        $endingSoonTenants = $endingSoonTenants
            ->sortBy('rent_days_left')
            ->values();

        $upcomingInvoiceTenants = $upcomingInvoiceTenants
            ->sortBy([
                ['invoice_days_left', 'asc'],
                ['location_name', 'asc'],
                ['username', 'asc'],
            ])
            ->values();

        $monthlyInvoiceTotal = $upcomingInvoiceTenants
            ->sum(fn($tenant) => is_numeric($tenant['invoice_price'] ?? null)
                ? (float) $tenant['invoice_price']
                : 0);

        $dashboardCards = $this->dashboardCards(
            roomStatuses: $roomStatuses,
            statusCounts: $statusCounts,
            endingSoonCount: $endingSoonTenants->count(),
            invoiceCount: $upcomingInvoiceTenants->count()
        );

        return view('manager.dashboard', [
            'roomSections' => collect($roomSections)->values(),
            'hasRooms' => !empty($roomSections),
            'dashboardCards' => $dashboardCards,
            'statusCounts' => $statusCounts,
            'roomTypes' => $roomTypes,
            'locations' => $locations,
            'locationCounts' => $locationCounts,
            'roomStatuses' => $roomStatuses,
            'clientsByLocation' => $clientsByLocation,
            'recentClients' => $recentClients,
            'bookingsByDate' => $bookingsByDate,

            'rentAlertDays' => $rentAlertDays,
            'endingSoonTenants' => $endingSoonTenants,
            'upcomingInvoiceTenants' => $upcomingInvoiceTenants,
            'monthlyInvoiceTotal' => $monthlyInvoiceTotal,
            'monthlyInvoiceTotalText' => $this->formatMoney($monthlyInvoiceTotal),
            'nextInvoiceDate' => $nextInvoiceDate,
            'nextInvoiceDateText' => $this->formatDate($nextInvoiceDate),
            'invoiceWindowStart' => $invoiceWindowStart,
            'invoiceWindowStartText' => $this->formatDate($invoiceWindowStart),
            'isInvoiceWindow' => $isInvoiceWindow,

            'filters' => [
                'search' => $request->input('search'),
                'location_id' => $request->input('location_id'),
                'room_type_id' => $request->input('room_type_id'),
                'status' => $request->input('status'),
                'rent_alert_days' => $rentAlertDays,
                'has' => $request->filled('search')
                    || $request->filled('location_id')
                    || $request->filled('room_type_id')
                    || $request->filled('status')
                    || $request->filled('rent_alert_days'),
                'reset_url' => route('dashboard.manager'),
            ],

            'bookingChart' => [
                'categories' => $bookingsByDate->keys()->values(),
                'data' => $bookingsByDate->values(),
            ],
        ]);
    }

    private function decorateRoom(
        array $room,
        string $locationId,
        string $roomTypeName,
        mixed $roomSize,
        mixed $roomPrice,
        array $statusInfo
    ): array {
        $roomId = $room['id'] ?? null;
        $status = (string) ($room['status'] ?? '');

        return [
            'id' => $roomId,
            'location_id' => $locationId,
            'room_name' => $room['room_name'] ?? __('Room'),
            'building_name' => $room['building_name'] ?? '-',
            'floor_name' => $room['floor_name'] ?? '-',
            'room_type_name' => $roomTypeName,
            'room_size' => $roomSize ?: '-',
            'room_price' => $roomPrice,
            'room_price_text' => $this->formatMoney($roomPrice),
            'status' => $status,
            'status_name' => $statusInfo['name'] ?? __('Unknown Status'),
            'status_badge' => $statusInfo['badge'] ?? 'badge bg-secondary text-secondary-fg',
            'status_class' => $statusInfo['class'] ?? 'bg-secondary text-secondary-fg',
            'status_text' => $statusInfo['text'] ?? 'text-secondary',
            'is_ending_soon' => false,
            'ending_tenant_name' => null,
            'ending_tenant_days_text' => null,
            'ending_tenant_url' => null,
            'show_url' => $roomId ? route('room.show', [
                'room_id' => $roomId,
                'location_id' => $locationId,
            ]) : null,
            'booking_url' => ($roomId && $status === '0') ? route('room.booking', [
                'room_id' => $roomId,
                'location_id' => $locationId,
            ]) : null,
            'can_book' => $roomId && $status === '0',
        ];
    }

    private function decorateClient(array $client, array $room, string $locationName): array
    {
        $status = Active::getStatus($client['status'] ?? null);

        $clientId = $client['id'] ?? null;
        $locationId = $room['location_id'] ?? null;
        $clientImagePath = $client['client_image'] ?? null;

        return [
            'id' => $clientId,
            'username' => $client['username'] ?? __('Unknown Tenant'),
            'initial' => $this->initial($client['username'] ?? null),
            'phone_number' => $client['phone_number'] ?? null,
            'email' => $client['email'] ?? null,
            'contact_text' => $client['email'] ?? $client['phone_number'] ?? __('No contact'),
            'gender' => $client['gender'] ?? null,
            'image' => $this->imageUrl($clientImagePath),
            'has_image' => !blank($clientImagePath),
            'start_rental_date' => $client['start_rental_date'] ?? null,
            'start_rental_date_text' => $this->formatDate($client['start_rental_date'] ?? null),
            'end_rental_date' => $client['end_rental_date'] ?? null,
            'end_rental_date_text' => $this->formatDate($client['end_rental_date'] ?? null),
            'status' => $client['status'] ?? null,
            'status_name' => $status['name'] ?? __('Unknown'),
            'status_badge' => $status['badge'] ?? 'badge bg-secondary text-secondary-fg',
            'status_text' => $status['text'] ?? 'text-secondary',
            'location_id' => $locationId,
            'location_name' => $locationName,
            'room_id' => $room['id'] ?? null,
            'room_name' => $room['room_name'],
            'room_meta' => $room['building_name'] . ' · ' . __('Floor') . ' ' . $room['floor_name'],
            'room_type_name' => $room['room_type_name'],
            'room_price' => $room['room_price'],
            'room_price_text' => $room['room_price_text'],
            'room_show_url' => $room['show_url'],
            'client_detail_url' => ($clientId && $locationId)
                ? route('clients.edit', [
                    'id' => $clientId,
                    'locationId' => $locationId,
                ])
                : null,
        ];
    }

    private function dashboardCards(
        array $roomStatuses,
        array $statusCounts,
        int $endingSoonCount,
        int $invoiceCount
    ): array {
        $icons = [
            0 => 'door',
            1 => 'door-off',
            2 => 'calendar-week',
            3 => 'tool',
        ];

        $cards = [
            [
                'label' => __('All Rooms'),
                'value' => $statusCounts['all'] ?? 0,
                'icon' => 'home',
                'class' => 'bg-primary text-primary-fg',
                'subtext' => __('Assigned locations'),
            ],
        ];

        foreach ($roomStatuses as $key => $status) {
            $cards[] = [
                'label' => __($status['name'] ?? __('Unknown')),
                'value' => $statusCounts[$key] ?? 0,
                'icon' => $icons[$key] ?? 'circle',
                'class' => $status['class'] ?? 'bg-secondary text-secondary-fg',
                'subtext' => __('Room status'),
            ];
        }

        $cards[] = [
            'label' => __('Rent Ending'),
            'value' => $endingSoonCount,
            'icon' => 'alert-triangle',
            'class' => 'bg-danger-lt text-danger',
            'subtext' => __('Needs action'),
        ];

        $cards[] = [
            'label' => __('Invoices'),
            'value' => $invoiceCount,
            'icon' => 'receipt-2',
            'class' => 'bg-warning-lt text-warning',
            'subtext' => __('Next cycle'),
        ];

        return $cards;
    }

    private function nextInvoiceDate(Carbon $today): Carbon
    {
        if ($today->isSameDay($today->copy()->startOfMonth())) {
            return $today->copy();
        }

        return $today->copy()->addMonthNoOverflow()->startOfMonth();
    }

    private function parseDate(mixed $date): ?Carbon
    {
        if (blank($date)) {
            return null;
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function formatDate(mixed $date): string
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

    private function formatMoney(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '-';
        }

        return number_format((float) $value, 2) . '៛';
    }

    private function initial(?string $name): string
    {
        return mb_strtoupper(mb_substr($name ?: 'U', 0, 1));
    }

    private function imageUrl(?string $path): string
    {
        return !blank($path)
            ? apiBaseUrl() . $path
            : asset('images/default-avatar.png');
    }
}