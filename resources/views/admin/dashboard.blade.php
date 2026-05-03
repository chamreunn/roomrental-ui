@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Rental Management') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Operations Dashboard') }}
                    </h2>
                    <div class="text-secondary mt-1">
                        {{ __('Monitor room availability, expiring rentals, and upcoming invoice reminders.') }}
                    </div>
                </div>

                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <span class="badge bg-danger-lt text-danger">
                            <x-icon name="alert-triangle" />
                            {{ $endingSoonTenants->count() }}
                            {{ __('rent ending') }}
                        </span>

                        <span class="badge bg-warning-lt text-warning">
                            <x-icon name="receipt-2" />
                            {{ $upcomingInvoiceTenants->count() }}
                            {{ __('invoice reminders') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            {{-- Filter Bar --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ url()->current() }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-4 col-xl-3">
                                <label class="form-label">{{ __('Search') }}</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <x-icon name="search" />
                                    </span>
                                    <input type="text" name="search" value="{{ $filters['search'] }}"
                                        class="form-control" placeholder="{{ __('Search room or tenant') }}">
                                </div>
                            </div>

                            <div class="col-6 col-md-3 col-xl-2">
                                <label class="form-label">{{ __('Room Type') }}</label>
                                <select name="room_type_id" class="form-select">
                                    <option value="">{{ __('All room types') }}</option>
                                    @foreach ($roomTypes as $roomType)
                                        <option value="{{ $roomType['id'] }}" @selected($filters['room_type_id'] == $roomType['id'])>
                                            {{ $roomType['type_name'] ?? __('Unknown Type') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-xl-2">
                                <label class="form-label">{{ __('Room Status') }}</label>
                                <select name="status" class="form-select">
                                    <option value="">{{ __('All statuses') }}</option>
                                    @foreach ($roomStatuses as $statusKey => $status)
                                        <option value="{{ $statusKey }}" @selected((string) $filters['status'] === (string) $statusKey)>
                                            {{ __($status['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-2 col-xl-2">
                                <label class="form-label">{{ __('Rent Alert') }}</label>
                                <select name="rent_alert_days" class="form-select">
                                    @foreach ([7, 14, 30, 60] as $days)
                                        <option value="{{ $days }}" @selected((int) $rentAlertDays === $days)>
                                            {{ $days }} {{ __('days') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary w-100">
                                    <x-icon name="filter" />
                                    {{ __('Filter') }}
                                </button>
                            </div>

                            <div class="col-6 col-md-auto">
                                <a href="{{ $filters['reset_url'] }}" class="btn btn-outline-secondary w-100">
                                    <x-icon name="rotate-clockwise" />
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row row-cards mb-3">
                @foreach ($dashboardCards as $card)
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar {{ $card['class'] }}">
                                            <x-icon :name="$card['icon']" />
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary text-truncate">
                                            {{ $card['label'] }}
                                        </div>
                                        <div class="h2 mb-0">
                                            {{ $card['value'] }}
                                        </div>
                                        <div class="text-secondary small text-truncate">
                                            {{ $card['subtext'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Alert Center --}}
            <div class="row row-cards mb-3">

                {{-- Rent Ending Soon --}}
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-status-top bg-danger"></div>

                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <x-icon name="alert-circle" />
                                    {{ __('Rent Ending Soon') }}
                                </h3>
                                <div class="text-secondary small">
                                    {{ __('Within') }} {{ $rentAlertDays }} {{ __('days') }}
                                </div>
                            </div>
                            <div class="card-actions">
                                <span class="badge bg-danger-lt text-danger">
                                    {{ $endingSoonTenants->count() }}
                                </span>
                            </div>
                        </div>

                        @if ($endingSoonTenants->isEmpty())
                            <div class="card-body text-center py-5">
                                <span class="avatar avatar-xl bg-success-lt text-success mb-3">
                                    <x-icon name="circle-check" />
                                </span>
                                <h3>{{ __('No rent ending soon') }}</h3>
                                <div class="text-secondary">
                                    {{ __('No active tenants are close to the rental end date.') }}
                                </div>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($endingSoonTenants->take(6) as $tenant)
                                    <a href="{{ $tenant['client_detail_url'] ?? ($tenant['room_show_url'] ?? '#') }}"
                                        class="list-group-item list-group-item-action">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                @if ($tenant['has_image'])
                                                    <span class="avatar avatar-rounded">
                                                        <img src="{{ $tenant['image'] }}" alt="{{ $tenant['username'] }}">
                                                    </span>
                                                @else
                                                    <span class="avatar avatar-rounded bg-danger-lt text-danger">
                                                        {{ $tenant['initial'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="col text-truncate">
                                                <div class="fw-semibold text-truncate">
                                                    {{ $tenant['username'] }}
                                                </div>
                                                <div class="text-secondary small text-truncate">
                                                    {{ $tenant['room_name'] }} · {{ $tenant['location_name'] }}
                                                </div>
                                            </div>

                                            <div class="col-auto text-end">
                                                <span class="badge bg-danger-lt text-danger">
                                                    {{ $tenant['rent_days_left_text'] }}
                                                </span>
                                                <div class="text-secondary small mt-1">
                                                    {{ $tenant['rent_ends_at_text'] }}
                                                </div>
                                            </div>

                                            <div class="col-auto">
                                                <x-icon name="chevron-right" class="text-secondary" />
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="card-footer">
                                <span class="text-secondary small">
                                    {{ __('Click a tenant to open their detail page.') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Invoice Queue --}}
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-status-top bg-warning"></div>

                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <x-icon name="receipt" />
                                    {{ __('Invoice Queue') }}
                                </h3>
                                <div class="text-secondary small">
                                    {{ __('Invoice date') }}: {{ $nextInvoiceDateText }}
                                </div>
                            </div>
                            <div class="card-actions">
                                <span class="badge bg-warning-lt text-warning">
                                    {{ $monthlyInvoiceTotalText }}
                                </span>
                            </div>
                        </div>

                        @if (!$isInvoiceWindow)
                            <div class="card-body text-center py-5">
                                <span class="avatar avatar-xl bg-warning-lt text-warning mb-3">
                                    <x-icon name="calendar-time" />
                                </span>
                                <h3>{{ __('Not in reminder window') }}</h3>
                                <div class="text-secondary">
                                    {{ __('Reminder starts on') }} {{ $invoiceWindowStartText }}.
                                </div>
                            </div>
                        @elseif ($upcomingInvoiceTenants->isEmpty())
                            <div class="card-body text-center py-5">
                                <span class="avatar avatar-xl bg-success-lt text-success mb-3">
                                    <x-icon name="circle-check" />
                                </span>
                                <h3>{{ __('No invoice reminders') }}</h3>
                                <div class="text-secondary">
                                    {{ __('No active tenants match the next invoice period.') }}
                                </div>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($upcomingInvoiceTenants->take(6) as $tenant)
                                    <a href="{{ $tenant['client_detail_url'] ?? ($tenant['room_show_url'] ?? '#') }}"
                                        class="list-group-item list-group-item-action">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                @if ($tenant['has_image'])
                                                    <span class="avatar avatar-rounded">
                                                        <img src="{{ $tenant['image'] }}"
                                                            alt="{{ $tenant['username'] }}">
                                                    </span>
                                                @else
                                                    <span class="avatar avatar-rounded bg-warning-lt text-warning">
                                                        {{ $tenant['initial'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="col text-truncate">
                                                <div class="fw-semibold text-truncate">
                                                    {{ $tenant['username'] }}
                                                </div>
                                                <div class="text-secondary small text-truncate">
                                                    {{ $tenant['room_name'] }} · {{ $tenant['room_type_name'] }}
                                                </div>
                                            </div>

                                            <div class="col-auto text-end">
                                                <span class="badge bg-warning-lt text-warning">
                                                    {{ $tenant['invoice_price_text'] }}
                                                </span>
                                                <div class="text-secondary small mt-1">
                                                    {{ $tenant['invoice_days_left_text'] }}
                                                </div>
                                            </div>

                                            <div class="col-auto">
                                                <x-icon name="chevron-right" class="text-secondary" />
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Recent Tenants --}}
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-status-top bg-primary"></div>

                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <x-icon name="users" />
                                    {{ __('Recent Tenants') }}
                                </h3>
                                <div class="text-secondary small">
                                    {{ __('Latest rental check-ins') }}
                                </div>
                            </div>
                        </div>

                        @if ($recentClients->isEmpty())
                            <div class="card-body text-center py-5">
                                <span class="avatar avatar-xl bg-secondary-lt text-secondary mb-3">
                                    <x-icon name="user-off" />
                                </span>
                                <h3>{{ __('No clients') }}</h3>
                                <div class="text-secondary">
                                    {{ __('No recent clients found.') }}
                                </div>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach ($recentClients->take(6) as $client)
                                    <a href="{{ $client['client_detail_url'] ?? ($client['room_show_url'] ?? '#') }}"
                                        class="list-group-item list-group-item-action">
                                        <div class="row align-items-center">
                                            <div class="col-auto">
                                                @if ($client['has_image'])
                                                    <span class="avatar avatar-rounded">
                                                        <img src="{{ $client['image'] }}"
                                                            alt="{{ $client['username'] }}">
                                                    </span>
                                                @else
                                                    <span class="avatar avatar-rounded bg-primary-lt text-primary">
                                                        {{ $client['initial'] }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="col text-truncate">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="fw-semibold text-truncate">
                                                        {{ $client['username'] }}
                                                    </div>
                                                    <span class="{{ $client['status_badge'] }}">
                                                        {{ __($client['status_name']) }}
                                                    </span>
                                                </div>
                                                <div class="text-secondary small text-truncate">
                                                    {{ $client['room_name'] }} · {{ $client['location_name'] }}
                                                </div>
                                            </div>

                                            <div class="col-auto text-end text-secondary small">
                                                {{ $client['start_rental_date_text'] }}
                                            </div>

                                            <div class="col-auto">
                                                <x-icon name="chevron-right" class="text-secondary" />
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="card-footer">
                                <a href="{{ route('clients.show_location') }}" class="btn btn-sm w-100">
                                    {{ __('View all clients') }}
                                    <x-icon name="arrow-right" />
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Activity Chart --}}
            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            <x-icon name="chart-area-line" />
                            {{ __('Booking Activity') }}
                        </h3>
                        <div class="text-secondary small">
                            {{ __('Recent bookings grouped by rental start date.') }}
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div id="booking-stats-chart"></div>
                </div>
            </div>

            {{-- Booking Room List --}}
            @if (!$hasRooms)
                <div class="card">
                    <div class="card-body text-center py-5">
                        <span class="avatar avatar-xl bg-secondary-lt text-secondary mb-3">
                            <x-icon name="building-off" />
                        </span>
                        <h3>{{ __('No rooms found') }}</h3>
                        <div class="text-secondary">
                            {{ __('Try changing your filter or add rooms first.') }}
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">
                                <x-icon name="door-enter" />
                                {{ __('Room Booking Board') }}
                            </h3>
                            <div class="text-secondary small">
                                {{ __('Choose an available room and continue booking.') }}
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="accordion accordion-flush" id="roomBookingAccordion">
                            @foreach ($roomSections as $section)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $loop->index }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                            <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                                <div>
                                                    <div class="fw-semibold">
                                                        <x-icon name="map-pin" />
                                                        {{ $section['location_name'] }}
                                                    </div>
                                                    <div class="text-secondary small">
                                                        {{ $section['room_count'] }} {{ __('rooms') }}
                                                    </div>
                                                </div>

                                                <span class="badge bg-primary-lt text-primary">
                                                    {{ $section['room_count'] }}
                                                </span>
                                            </div>
                                        </button>
                                    </h2>

                                    <div id="collapse-{{ $loop->index }}"
                                        class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                        data-bs-parent="#roomBookingAccordion">
                                        <div class="accordion-body p-0">

                                            @foreach ($section['types'] as $type)
                                                <div class="border-bottom">
                                                    <div class="bg-body-tertiary px-3 py-2">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div>
                                                                <div class="fw-semibold">
                                                                    {{ $type['room_type_name'] }}
                                                                </div>
                                                                <div class="text-secondary small">
                                                                    {{ $type['room_count'] }} {{ __('rooms') }}
                                                                </div>
                                                            </div>

                                                            <span class="badge bg-secondary-lt text-secondary">
                                                                {{ $type['room_count'] }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="list-group list-group-flush">
                                                        @foreach ($type['rooms'] as $room)
                                                            <div class="list-group-item">
                                                                <div class="row g-3 align-items-center">

                                                                    {{-- Room identity --}}
                                                                    <div class="col-md-4">
                                                                        <div class="d-flex align-items-center">
                                                                            <span
                                                                                class="avatar me-3 {{ $room['can_book'] ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                                                                <x-icon name="door" />
                                                                            </span>

                                                                            <div class="min-w-0">
                                                                                <div
                                                                                    class="d-flex align-items-center gap-2">
                                                                                    <div class="fw-semibold text-truncate">
                                                                                        {{ $room['room_name'] }}
                                                                                    </div>

                                                                                    @if ($room['is_ending_soon'])
                                                                                        <span
                                                                                            class="status-dot status-dot-animated bg-red"></span>
                                                                                    @endif
                                                                                </div>

                                                                                <div
                                                                                    class="text-secondary small text-truncate">
                                                                                    {{ $room['building_name'] }}
                                                                                    ·
                                                                                    {{ __('Floor') }}
                                                                                    {{ $room['floor_name'] }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Room details --}}
                                                                    <div class="col-md-3">
                                                                        <div class="row g-2">
                                                                            <div class="col-6">
                                                                                <div class="text-secondary small">
                                                                                    {{ __('Size') }}
                                                                                </div>
                                                                                <div class="fw-medium">
                                                                                    {{ $room['room_size'] }}
                                                                                </div>
                                                                            </div>

                                                                            <div class="col-6">
                                                                                <div class="text-secondary small">
                                                                                    {{ __('Type') }}
                                                                                </div>
                                                                                <div class="fw-medium text-truncate">
                                                                                    {{ $room['room_type_name'] }}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Price and status --}}
                                                                    <div class="col-md-2">
                                                                        <div class="text-secondary small">
                                                                            {{ __('Monthly Price') }}
                                                                        </div>
                                                                        <div class="fw-bold">
                                                                            {{ $room['room_price_text'] }}
                                                                        </div>

                                                                        <div class="mt-1">
                                                                            <span class="{{ $room['status_badge'] }}">
                                                                                {{ __($room['status_name']) }}
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Ending soon --}}
                                                                    <div class="col-md-2">
                                                                        @if ($room['is_ending_soon'])
                                                                            @if ($room['ending_tenant_url'])
                                                                                <a href="{{ $room['ending_tenant_url'] }}"
                                                                                    class="text-decoration-none">
                                                                                    <span
                                                                                        class="badge bg-danger-lt text-danger mb-1">
                                                                                        {{ __('Ending Soon') }}
                                                                                    </span>
                                                                                    <div class="small text-truncate">
                                                                                        {{ $room['ending_tenant_name'] }}
                                                                                    </div>
                                                                                    <div class="text-secondary small">
                                                                                        {{ $room['ending_tenant_days_text'] }}
                                                                                    </div>
                                                                                </a>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-danger-lt text-danger mb-1">
                                                                                    {{ __('Ending Soon') }}
                                                                                </span>
                                                                                <div class="small text-truncate">
                                                                                    {{ $room['ending_tenant_name'] }}
                                                                                </div>
                                                                                <div class="text-secondary small">
                                                                                    {{ $room['ending_tenant_days_text'] }}
                                                                                </div>
                                                                            @endif
                                                                        @else
                                                                            <span class="text-secondary small">
                                                                                {{ __('No active alert') }}
                                                                            </span>
                                                                        @endif
                                                                    </div>

                                                                    {{-- Actions --}}
                                                                    <div class="col-md-1">
                                                                        <div
                                                                            class="btn-list justify-content-end flex-nowrap">
                                                                            @if ($room['show_url'])
                                                                                <a href="{{ $room['show_url'] }}"
                                                                                    class="btn btn-icon btn-outline-primary"
                                                                                    title="{{ __('View') }}">
                                                                                    <x-icon name="eye" />
                                                                                </a>
                                                                            @endif

                                                                            @if ($room['can_book'])
                                                                                <a href="{{ $room['booking_url'] }}"
                                                                                    class="btn btn-icon btn-primary"
                                                                                    title="{{ __('Book') }}">
                                                                                    <x-icon name="calendar-plus" />
                                                                                </a>
                                                                            @else
                                                                                <button type="button"
                                                                                    class="btn btn-icon btn-secondary disabled"
                                                                                    title="{{ __('Unavailable') }}">
                                                                                    <x-icon name="lock" />
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartElement = document.querySelector('#booking-stats-chart');

            if (!chartElement || typeof ApexCharts === 'undefined') {
                return;
            }

            const chartData = @json($bookingChart);

            const chart = new ApexCharts(chartElement, {
                chart: {
                    type: 'bar',
                    height: 300,
                    toolbar: {
                        show: false
                    }
                },
                series: [{
                    name: "{{ __('Bookings') }}",
                    data: chartData.data
                }],
                xaxis: {
                    categories: chartData.categories
                },
                yaxis: {
                    min: 0,
                    forceNiceScale: true
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        columnWidth: '38%'
                    }
                },
                dataLabels: {
                    enabled: false
                },
                grid: {
                    strokeDashArray: 4
                },
                noData: {
                    text: "{{ __('No booking data') }}"
                },
                tooltip: {
                    y: {
                        formatter: function(value) {
                            return value + " {{ __('bookings') }}";
                        }
                    }
                }
            });

            chart.render();
        });
    </script>
@endpush
