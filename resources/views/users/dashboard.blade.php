@extends('layouts.app')

@section('content')
    <div class="row row-cards g-3">
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
                                        <div class="card room-card text-center flex-shrink-0" style="width: 170px; min-width: 160px;">
                                            <div class="card-body p-2">
                                                <h5 class="fw-bold text-truncate mb-1">{{ $room['room_name'] }}</h5>
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

<style>
    .room-scroll.dragging {
        cursor: grabbing;
        cursor: -webkit-grabbing;
    }

    .room-scroll {
        cursor: grab;
        cursor: -webkit-grab;
        overflow-x: auto;
        scroll-behavior: smooth;
    }
</style>

@push('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".room-scroll-wrapper").forEach(wrapper => {
                const scrollContainer = wrapper.querySelector(".room-scroll");
                const btnLeft = wrapper.querySelector(".scroll-left");
                const btnRight = wrapper.querySelector(".scroll-right");
                const SCROLL_AMOUNT = 300;

                let isDown = false;
                let startX;
                let scrollLeft;
                let velocity = 0;
                let momentumID;

                function updateButtons() {
                    const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;

                    if (maxScrollLeft <= 0) {
                        btnLeft.style.opacity = 0;
                        btnRight.style.opacity = 0;
                        btnLeft.style.pointerEvents = "none";
                        btnRight.style.pointerEvents = "none";
                        return;
                    }

                    btnLeft.style.opacity = scrollContainer.scrollLeft > 5 ? "1" : "0";
                    btnLeft.style.pointerEvents = scrollContainer.scrollLeft > 5 ? "auto" : "none";

                    btnRight.style.opacity = scrollContainer.scrollLeft < maxScrollLeft - 5 ? "1" : "0";
                    btnRight.style.pointerEvents = scrollContainer.scrollLeft < maxScrollLeft - 5 ? "auto" : "none";
                }

                // Button click scrolling
                btnLeft.addEventListener("click", () => {
                    scrollContainer.scrollBy({ left: -SCROLL_AMOUNT, behavior: "smooth" });
                });
                btnRight.addEventListener("click", () => {
                    scrollContainer.scrollBy({ left: SCROLL_AMOUNT, behavior: "smooth" });
                });

                // Stop momentum on user interaction
                function stopMomentum() {
                    cancelAnimationFrame(momentumID);
                    velocity = 0;
                }

                // Drag / swipe support with momentum
                scrollContainer.addEventListener("mousedown", (e) => {
                    isDown = true;
                    scrollContainer.classList.add("dragging");
                    startX = e.pageX - scrollContainer.offsetLeft;
                    scrollLeft = scrollContainer.scrollLeft;
                    stopMomentum();
                });

                scrollContainer.addEventListener("mouseleave", () => {
                    isDown = false;
                    scrollContainer.classList.remove("dragging");
                    applyMomentum();
                });

                scrollContainer.addEventListener("mouseup", () => {
                    isDown = false;
                    scrollContainer.classList.remove("dragging");
                    applyMomentum();
                });

                scrollContainer.addEventListener("mousemove", (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - scrollContainer.offsetLeft;
                    const walk = (x - startX);
                    velocity = walk; // track last move for momentum
                    scrollContainer.scrollLeft = scrollLeft - walk;
                });

                // Touch support
                scrollContainer.addEventListener("touchstart", (e) => {
                    startX = e.touches[0].pageX - scrollContainer.offsetLeft;
                    scrollLeft = scrollContainer.scrollLeft;
                    stopMomentum();
                });

                scrollContainer.addEventListener("touchmove", (e) => {
                    const x = e.touches[0].pageX - scrollContainer.offsetLeft;
                    const walk = (x - startX);
                    velocity = walk;
                    scrollContainer.scrollLeft = scrollLeft - walk;
                });

                scrollContainer.addEventListener("touchend", applyMomentum);

                // Momentum scrolling function
                function applyMomentum() {
                    const decay = 0.95;
                    const minVelocity = 0.5;

                    function step() {
                        scrollContainer.scrollLeft -= velocity;
                        velocity *= decay;

                        if (Math.abs(velocity) > minVelocity) {
                            momentumID = requestAnimationFrame(step);
                        } else {
                            cancelAnimationFrame(momentumID);
                        }
                    }
                    momentumID = requestAnimationFrame(step);
                }

                scrollContainer.addEventListener("scroll", updateButtons);
                window.addEventListener("resize", updateButtons);

                updateButtons();
            });
        });
    </script>

@endpush
