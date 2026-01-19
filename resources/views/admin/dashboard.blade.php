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
                                        <div class="font-weight-medium">{{ __($status['name']) }}</div>
                                        <div class="text-secondary">
                                            {{ $statusCounts[$statusKey] ?? 0 }}
                                            {{ strtolower(__($status['name'])) }}
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
            <div class="row g-3 row-deck row-cards align-items-stretch">
                {{-- ===== Left: Recent Clients ===== --}}
                <div class="row row-cards">

                    {{-- ================= LEFT: Recent Clients ================= --}}
                    <div class="col-md-4 col-lg-4 d-flex">
                        <div class="card flex-fill d-flex flex-column">

                            <div class="card-header">
                                <h3 class="card-title">{{ __('dashboard.recent_clients') }}</h3>
                            </div>

                            {{-- Empty State --}}
                            @if ($recentClients->isEmpty())
                                <x-empty-state title="{{ __('dashboard.no_clients') }}"
                                    message="{{ __('dashboard.no_clients_message') }}" svg="svgs/no_result.svg"
                                    width="150px" />
                            @else
                                <div class="table-responsive flex-fill">
                                    <table class="table card-table table-vcenter">
                                        <thead>
                                            <tr>
                                                <th>{{ __('dashboard.client') }}</th>
                                                <th>{{ __('dashboard.check_in') }}</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($recentClients->take(5) as $client)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">

                                                            {{-- Avatar --}}
                                                            @if (empty($client['image']))
                                                                <span
                                                                    class="avatar me-3 bg-{{ ($client['gender'] ?? '') === 'ប្រុស' ? 'blue' : 'pink' }} text-white fw-bold">
                                                                    {{ strtoupper(substr($client['username'], 0, 1)) }}
                                                                </span>
                                                            @else
                                                                <span class="avatar me-3"
                                                                    style="background-image: url('{{ $client['image'] }}'); background-size: cover;">
                                                                </span>
                                                            @endif

                                                            <div>
                                                                <div class="font-weight-medium text-primary">
                                                                    <strong>{{ ucfirst($client['username']) }}</strong>

                                                                    @if (!empty($client['clientstatus']))
                                                                        <span class="ms-2">
                                                                            <span
                                                                                class="{{ $client['clientstatus']['badge'] }}">
                                                                                {{ __($client['clientstatus']['name']) }}
                                                                            </span>
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                <div class="text-muted">
                                                                    {{ $client['room']['room_name'] ?? 'N/A' }}
                                                                    {{ $client['room']['building_name'] ?? '' }}
                                                                    ({{ __('room.floor_name') }}
                                                                    {{ $client['room']['floor_name'] ?? 'N/A' }})
                                                                </div>

                                                                <div class="text-muted small">
                                                                    {{ $client['location_name'] ?? '' }}
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </td>

                                                    <td class="text-nowrap">
                                                        {{ \Carbon\Carbon::parse($client['start_rental_date'])->translatedFormat('d M Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="card-footer mt-auto">
                                <a href="{{ route('clients.show_location') }}" class="btn btn-sm w-100">
                                    {{ __('dashboard.view_all_clients') }}
                                    <x-icon name="arrow-right" />
                                </a>
                            </div>

                        </div>
                    </div>

                    {{-- ================= RIGHT: Booking Overview Chart ================= --}}
                    <div class="col-lg-8 d-flex">
                        <div class="card flex-fill">

                            <div class="card-header">
                                <h3 class="card-title">{{ __('dashboard.booking_overview') }}</h3>
                            </div>

                            <div class="card-body d-flex align-items-center justify-content-center">
                                <div id="booking-stats-chart" class="w-100" style="min-height: 350px;">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            @foreach ($groupedRooms as $locationName => $roomTypes)
                <div class="mb-4">
                    <h3 class="mb-3 text-primary">{{ $locationName }}</h3>

                    @foreach ($roomTypes as $roomTypeName => $statuses)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h4 class="card-title mb-0">{{ $roomTypeName }}</h4>
                            <span class="text-muted small">
                                {{ collect($statuses)->flatten(1)->count() }} {{ __('room.room') }}
                            </span>
                        </div>

                        <!-- Horizontal Scroll Wrapper -->
                        <div class="room-scroll-wrapper position-relative">
                            <div class="room-scroll d-flex gap-3 pb-2" style="overflow-x: auto; scroll-behavior: smooth;">
                                @foreach ($statuses as $statusKey => $rooms)
                                    @foreach ($rooms as $room)
                                        <div class="card room-card text-center flex-shrink-0 shadow-sm border-0"
                                            style="width: 170px; min-width: 160px;">
                                            <div class="card-body p-2">
                                                <h5
                                                    class="fw-bold text-truncate mb-1 d-flex align-items-center justify-content-center">
                                                    {{ $room['room_name'] }}
                                                    @if (!empty($room['is_ending_soon']) && $room['is_ending_soon'])
                                                        <span class="status-dot status-dot-animated bg-red d-block ms-2"
                                                            style="width: 8px; height: 8px;"
                                                            title="Rental ending soon"></span>
                                                    @endif
                                                </h5>

                                                <div class="text-muted small mb-2">
                                                    {{ $room['building_name'] }} • {{ $room['floor_name'] }}
                                                </div>

                                                <span class="badge {{ $room['status_class'] }} mb-2">
                                                    {{ __($room['status_name']) }}
                                                </span>

                                                <div class="small text-secondary mb-2">
                                                    {{ $room['room_type']['room_size'] ?? '' }}
                                                </div>

                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('room.show', ['room_id' => $room['id'], 'location_id' => $room['location']['id']]) }}"
                                                        class="btn btn-sm btn-outline-primary px-3 w-100">
                                                        {{ __('room.view') }}
                                                    </a>
                                                    @if ($room['status'] == '0')
                                                        <a href="{{ route('room.booking', ['room_id' => $room['id'], 'location_id' => $room['location']['id']]) }}"
                                                            class="btn btn-sm btn-primary px-3 w-100">
                                                            {{ __('room.book') }}
                                                        </a>
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
                    const scrollWidth = Math.ceil(scrollContainer.scrollWidth);
                    const clientWidth = Math.ceil(scrollContainer.clientWidth);
                    const maxScrollLeft = scrollWidth - clientWidth;
                    const totalCards = scrollContainer.querySelectorAll(".room-card").length;

                    // ✅ Force showing arrows if there are many cards (even when screen is large)
                    const forceShow = totalCards > 4;

                    if (scrollWidth <= clientWidth + 2 && !forceShow) {
                        btnLeft.style.opacity = "0";
                        btnRight.style.opacity = "0";
                        btnLeft.style.pointerEvents = "none";
                        btnRight.style.pointerEvents = "none";
                        return;
                    }

                    // ✅ Smoothly show/hide based on scroll position
                    btnLeft.style.opacity = scrollContainer.scrollLeft > 10 ? "1" : "0";
                    btnLeft.style.pointerEvents = scrollContainer.scrollLeft > 10 ? "auto" : "none";

                    btnRight.style.opacity = scrollContainer.scrollLeft < maxScrollLeft - 10 ? "1" : "0";
                    btnRight.style.pointerEvents = scrollContainer.scrollLeft < maxScrollLeft - 10 ?
                        "auto" : "none";
                }

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

                scrollContainer.addEventListener("scroll", updateButtons);
                window.addEventListener("resize", updateButtons);

                updateButtons();
            });
        });
    </script>
@endpush
