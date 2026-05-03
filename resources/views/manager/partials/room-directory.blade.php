@if (!$hasRooms)
    <div class="card">
        <div class="card-body text-center py-5">
            <span class="avatar avatar-xl bg-secondary-lt text-secondary mb-3">
                <x-icon name="building-off" />
            </span>
            <h3>{{ __('No rooms found') }}</h3>
            <div class="text-secondary">
                {{ __('Try changing your filter or ask an admin to assign a location.') }}
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-header border-0 pb-0">
            <div>
                <div class="text-secondary small mb-1">
                    {{ __('Room Directory') }}
                </div>
                <h3 class="card-title mb-0">
                    <x-icon name="door-enter" />
                    {{ __('Managed Rooms') }}
                </h3>
            </div>

            <div class="card-actions">
                <span class="badge bg-primary-lt text-primary">
                    {{ $statusCounts['all'] ?? 0 }} {{ __('rooms') }}
                </span>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3">
                @foreach ($roomSections as $section)
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <span class="avatar avatar-sm bg-primary-lt text-primary me-2">
                                    <x-icon name="map-pin" />
                                </span>

                                <div>
                                    <div class="fw-bold">
                                        {{ $section['location_name'] }}
                                    </div>
                                    <div class="text-secondary small">
                                        {{ $section['room_count'] }} {{ __('rooms') }}
                                        ·
                                        {{ count($section['types']) }} {{ __('room types') }}
                                    </div>
                                </div>
                            </div>

                            <span class="badge bg-primary-lt text-primary">
                                {{ $section['room_count'] }}
                            </span>
                        </div>

                        <div class="row g-3">
                            @foreach ($section['types'] as $type)
                                <div class="col-12 col-xl-6">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <div>
                                                <h4 class="card-title mb-1">
                                                    {{ $type['room_type_name'] }}
                                                </h4>
                                                <div class="text-secondary small">
                                                    {{ $type['room_count'] }} {{ __('rooms in this type') }}
                                                </div>
                                            </div>

                                            <div class="card-actions">
                                                <span class="badge bg-secondary-lt text-secondary">
                                                    {{ collect($type['rooms'])->where('can_book', true)->count() }}
                                                    {{ __('available') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="list-group list-group-flush">
                                            @foreach ($type['rooms'] as $room)
                                                <div class="list-group-item">
                                                    <div class="row g-2 align-items-center">
                                                        <div class="col-auto">
                                                            <span
                                                                class="avatar avatar-sm {{ $room['can_book'] ? 'bg-success-lt text-success' : 'bg-secondary-lt text-secondary' }}">
                                                                <x-icon name="door" />
                                                            </span>
                                                        </div>

                                                        <div class="col text-truncate">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <a href="{{ $room['show_url'] ?? '#' }}"
                                                                    class="fw-semibold text-reset text-decoration-none text-truncate">
                                                                    {{ $room['room_name'] }}
                                                                </a>

                                                                @if ($room['is_ending_soon'])
                                                                    <span
                                                                        class="status-dot status-dot-animated bg-red"></span>
                                                                @endif
                                                            </div>

                                                            <div class="text-secondary small text-truncate">
                                                                {{ $room['building_name'] }}
                                                                ·
                                                                {{ __('Floor') }} {{ $room['floor_name'] }}
                                                                ·
                                                                {{ $room['room_size'] }}
                                                            </div>
                                                        </div>

                                                        <div class="col-auto d-none d-md-block text-end">
                                                            <div class="fw-bold text-success">
                                                                {{ $room['room_price_text'] }}
                                                            </div>
                                                            <div class="text-secondary small">
                                                                {{ __('per month') }}
                                                            </div>
                                                        </div>

                                                        <div class="col-auto d-none d-lg-block">
                                                            <span class="{{ $room['status_badge'] }}">
                                                                {{ __($room['status_name']) }}
                                                            </span>
                                                        </div>

                                                        <div class="col-auto">
                                                            <div class="btn-list flex-nowrap">
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
                                                                        class="btn btn-icon btn-outline-secondary disabled"
                                                                        title="{{ __('Unavailable') }}">
                                                                        <x-icon name="lock" />
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @if ($room['is_ending_soon'])
                                                        <div class="row mt-2">
                                                            <div class="col-auto">
                                                                <span class="avatar avatar-xs bg-danger-lt text-danger">
                                                                    <x-icon name="alert-triangle" />
                                                                </span>
                                                            </div>

                                                            <div class="col text-truncate">
                                                                @if ($room['ending_tenant_url'])
                                                                    <a href="{{ $room['ending_tenant_url'] }}"
                                                                        class="small text-danger text-decoration-none text-truncate d-block">
                                                                        {{ __('Ending Soon') }}:
                                                                        {{ $room['ending_tenant_name'] }}
                                                                        ·
                                                                        {{ $room['ending_tenant_days_text'] }}
                                                                    </a>
                                                                @else
                                                                    <div class="small text-danger text-truncate">
                                                                        {{ __('Ending Soon') }}:
                                                                        {{ $room['ending_tenant_name'] }}
                                                                        ·
                                                                        {{ $room['ending_tenant_days_text'] }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (!$loop->last)
                            <hr class="my-4">
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
