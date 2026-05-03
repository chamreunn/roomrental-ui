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
                        {{ __('Manager Dashboard') }}
                    </h2>
                    <div class="text-secondary mt-1">
                        {{ __('Review assigned locations, rent alerts, invoice reminders and room availability.') }}
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

            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('dashboard.manager') }}">
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
                                <label class="form-label">{{ __('location.name') }}</label>
                                <select name="location_id" class="form-select tom-select">
                                    <option value="">{{ __('All locations') }}</option>
                                    @foreach ($locations as $location)
                                        <option value="{{ $location['location_id'] }}" @selected((string) $filters['location_id'] === (string) $location['location_id'])>
                                            {{ $location['location_name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-xl-2">
                                <label class="form-label">{{ __('Room Type') }}</label>
                                <select name="room_type_id" class="form-select tom-select">
                                    <option value="">{{ __('All room types') }}</option>
                                    @foreach ($roomTypes as $roomType)
                                        <option value="{{ $roomType['id'] }}" @selected((string) $filters['room_type_id'] === (string) $roomType['id'])>
                                            {{ $roomType['type_name'] ?? __('Unknown Type') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-3 col-xl-2">
                                <label class="form-label">{{ __('Room Status') }}</label>
                                <select name="status" class="form-select tom-select">
                                    <option value="">{{ __('All statuses') }}</option>
                                    @foreach ($roomStatuses as $statusKey => $status)
                                        <option value="{{ $statusKey }}" @selected((string) $filters['status'] === (string) $statusKey)>
                                            {{ __($status['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-2 col-xl-1">
                                <label class="form-label">{{ __('Rent Alert') }}</label>
                                <select name="rent_alert_days" class="form-select">
                                    @foreach ([7, 14, 30, 60] as $days)
                                        <option value="{{ $days }}" @selected((int) $rentAlertDays === $days)>
                                            {{ $days }}
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

            <div class="row row-cards mb-3">
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

            @include('manager.partials.room-directory')
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
