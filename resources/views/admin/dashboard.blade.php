@extends('layouts.app')

@section('content')
    <div class="row g-3">
        {{-- ===== Summary Cards ===== --}}
        <div class="col-12">
            <div class="row row-cards">
                {{-- All Rooms --}}
                <div class="col-sm-6 col-lg-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="bg-primary text-white avatar">
                                        <x-icon name="home" />
                                    </span>
                                </div>
                                <div class="col">
                                    <div class="font-weight-medium">{{ __('dashboard.all_rooms') }}</div>
                                    <div class="text-secondary">{{ $statusCounts['all'] }} {{ __('dashboard.room') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Loop through each room status --}}
                @foreach ($roomStatuses as $statusKey => $status)
                    <div class="col-sm-6 col-lg-2">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="{{ $status['class'] }} avatar">
                                            @switch($statusKey)
                                                @case(0)
                                                    <x-icon name="door" />
                                                @break

                                                @case(1)
                                                    <x-icon name="door-off" />
                                                @break

                                                @case(2)
                                                    <x-icon name="calendar-week" />
                                                @break

                                                @case(3)
                                                    <x-icon name="tool" />
                                                @break
                                            @endswitch
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium">បន្ទប់{{ $status['name'] }}</div>
                                        <div class="text-secondary">
                                            {{ $statusCounts[$statusKey] ?? 0 }}
                                            {{ strtolower($status['name']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12">
            <div class="row g-3">
                {{-- ===== Left: Recent Clients ===== --}}
                <div class="col-md-4 col-lg-4 h-100">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('dashboard.recent_clients') }}</h3>
                        </div>

                        {{-- Empty State --}}
                        @if (empty($recentClients))
                            <x-empty-state title="{{ __('dashboard.no_clients') }}"
                                message="{{ __('dashboard.no_clients_message') }}" svg="svgs/no_result.svg"
                                width="150px" />
                        @else
                            <div class="table-responsive">
                                <table class="table card-table table-vcenter">
                                    <thead>
                                        <tr>
                                            <th>{{ __('dashboard.room') }}</th>
                                            <th>{{ __('dashboard.room') }}</th>
                                            <th>{{ __('dashboard.check_in') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentClients as $client)
                                            @if ($loop->index >= 5)
                                                @break
                                            @endif
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="avatar avatar-sm me-2 bg-{{ $client['gender'] == 'ប្រុស' ? 'blue' : 'pink' }} text-primary-fg">
                                                            {{ strtoupper(substr($client['username'], 0, 1)) }}
                                                        </span>
                                                        <div>
                                                            <div class="font-weight-medium text-primary">
                                                                {{ ucfirst($client['username']) }}
                                                            </div>
                                                            <div class="text-muted">{{ $client['phone_number'] }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if (!empty($client['room']))
                                                        <div class="font-weight-medium text-nowrap text-primary">
                                                            {{ $client['room']['room_name'] ?? 'N/A' }}
                                                        </div>
                                                        <div class="text-muted">
                                                            {{ $client['room']['building_name'] ?? 'N/A' }}
                                                            (Floor {{ $client['room']['floor_name'] ?? 'N/A' }})
                                                        </div>
                                                    @else
                                                        <div class="text-muted">Room data missing</div>
                                                    @endif
                                                </td>
                                                <td class="text-nowrap">
                                                    {{ \Carbon\Carbon::parse($client['start_rental_date'])->format('d M Y') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="card-footer">
                            <a href="#" class="btn btn-sm w-100">
                                {{ __('dashboard.view_all_clients') }}
                                <x-icon name="arrow-right" />
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ===== Right: Booking Overview Chart ===== --}}
                <div class="col-lg-8 h-100">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ __('dashboard.booking_overview') }}</h3>
                        </div>
                        <div class="card-body">
                            <div id="booking-stats-chart"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            @foreach ($groupedRooms as $locationName => $roomTypes)
                <div class="mb-b">
                    <h3 class="mb-3 text-primary">{{ $locationName }}</h3>

                    @foreach ($roomTypes as $roomTypeName => $statuses)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="card-title mb-0">{{ $roomTypeName }}</h4>
                            <span class="text-muted small">
                                {{ collect($statuses)->flatten(1)->count() }}
                                {{ __('room.room') }}
                            </span>
                        </div>

                        <!-- ✅ Horizontal Scroll Wrapper -->
                        <div class="room-scroll-wrapper position-relative">
                            <div class="room-scroll d-flex gap-3 pb-2" style="overflow: hidden;">
                                @foreach ($statuses as $statusKey => $rooms)
                                    @foreach ($rooms as $room)
                                        <div class="card room-card text-center border-0 shadow-sm flex-shrink-0"
                                            style="width: 160px; min-width: 160px; transform: translateX(0); transition: transform 0.4s ease;">
                                            <div class="card-body p-2">
                                                <h5 class="fw-bold text-truncate mb-1">{{ $room['room_name'] }}</h5>
                                                <div class="text-muted small mb-2">
                                                    {{ $room['building_name'] }} • {{ $room['floor_name'] }}
                                                </div>
                                                <span class="badge {{ $room['status_class'] }} mb-2">
                                                    {{ $room['status_name'] }}
                                                </span>
                                                <div class="small text-secondary mb-2">
                                                    {{ $room['room_type']['room_size'] ?? '' }}
                                                </div>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="" class="btn btn-sm btn-outline-primary px-3">
                                                        {{ __('room.view') }}
                                                    </a>
                                                    @if ($room['status'] == '0')
                                                        <button class="btn btn-sm btn-primary px-3">
                                                            {{ __('room.book') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>

                            <!-- Scroll buttons -->
                            <button
                                class="scroll-btn scroll-left btn btn-icon position-absolute top-50 start-0 translate-middle-y shadow-sm">
                                <x-icon name="arrow-left" />
                            </button>
                            <button
                                class="scroll-btn scroll-right btn btn-icon position-absolute top-50 end-0 translate-middle-y shadow-sm">
                                <x-icon name="arrow-right" />
                            </button>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const el = document.querySelector("#booking-stats-chart");
            if (!el) return;

            const bookings = @json($bookingsByDate);
            const categories = Object.keys(bookings);
            const data = Object.values(bookings);

            const chart = new ApexCharts(el, {
                chart: {
                    type: "area",
                    height: 260,
                    toolbar: {
                        show: false
                    },
                },
                series: [{
                    name: "{{ __('dashboard.bookings_count') }}",
                    data: data,
                }],
                xaxis: {
                    categories: categories,
                    title: {
                        text: "{{ __('dashboard.booking_date') }}"
                    }
                },
                yaxis: {
                    title: {
                        text: "{{ __('dashboard.clients_count') }}"
                    }
                },
                colors: ["#206bc4"],
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.1
                    }
                },
                stroke: {
                    curve: "smooth",
                    width: 3
                },
                dataLabels: {
                    enabled: false
                },
                tooltip: {
                    x: {
                        format: "yyyy-MM-dd"
                    },
                    y: {
                        formatter: val => `${val} {{ __('dashboard.bookings_count') }}`
                    }
                },
            });

            chart.render();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".room-scroll-wrapper").forEach(wrapper => {
                const scrollContainer = wrapper.querySelector(".room-scroll");
                const btnLeft = wrapper.querySelector(".scroll-left");
                const btnRight = wrapper.querySelector(".scroll-right");

                function updateButtons() {
                    // Sometimes scrollWidth is just 1–2px more due to subpixel rounding, so we use Math.ceil
                    const scrollWidth = Math.ceil(scrollContainer.scrollWidth);
                    const clientWidth = Math.ceil(scrollContainer.clientWidth);
                    const maxScrollLeft = scrollWidth - clientWidth;

                    // 🔒 Hide arrows if no horizontal overflow
                    if (scrollWidth <= clientWidth + 2) {
                        btnLeft.style.opacity = "0";
                        btnRight.style.opacity = "0";
                        btnLeft.style.pointerEvents = "none";
                        btnRight.style.pointerEvents = "none";
                        return;
                    }

                    // ✅ Smoothly show/hide arrows based on scroll position
                    btnLeft.style.opacity = scrollContainer.scrollLeft > 10 ? "1" : "0";
                    btnLeft.style.pointerEvents = scrollContainer.scrollLeft > 10 ? "auto" : "none";

                    btnRight.style.opacity = scrollContainer.scrollLeft < maxScrollLeft - 10 ? "1" : "0";
                    btnRight.style.pointerEvents = scrollContainer.scrollLeft < maxScrollLeft - 10 ?
                        "auto" : "none";
                }

                // Scroll controls
                btnLeft.addEventListener("click", () => {
                    scrollContainer.scrollBy({
                        left: -300,
                        behavior: "smooth"
                    });
                });
                btnRight.addEventListener("click", () => {
                    scrollContainer.scrollBy({
                        left: 300,
                        behavior: "smooth"
                    });
                });

                // Watch for scroll and resize
                scrollContainer.addEventListener("scroll", updateButtons);
                window.addEventListener("resize", updateButtons);

                // Initialize
                updateButtons();
            });
        });
    </script>
@endpush
