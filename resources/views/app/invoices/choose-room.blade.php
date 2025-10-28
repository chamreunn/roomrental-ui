@extends('layouts.app')

@section('content')

    @forelse ($rooms->groupBy('building_name') as $index => $group)
        @php
            $building = $index;
            $groupedRooms = $group;
            $color = $colors[$loop->index % count($colors)];
        @endphp

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header border-0 bg-{{ $color }}-lt d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <span class="fw-semibold text-{{ $color }} fs-5">
                        អាគារ <kbd class="bg-{{ $color }}-lt border-{{ $color }} text-{{ $color }}">{{ $building }}</kbd>
                    </span>
                </div>
                <div class="text-{{ $color }} small fw-semibold">
                    {{ $groupedRooms->count() }} {{ __('titles.rooms') }}
                </div>
            </div>

            <div class="list-group list-group-flush">
                @foreach ($groupedRooms as $room)
                    <a href="{{ route('invoice.create', [$room['id'], $room['location_id']]) }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 hover-shadow room-item"
                        style="transition: all 0.2s ease;">
                        {{-- ✅ Checkbox --}}
                        <div class="d-flex align-items-center flex-grow-1">

                            <div class="me-3">
                                <span class="avatar bg-{{ $color }}-lt text-{{ $color }} fw-bold">
                                    {{ strtoupper(substr($room['room_name'], 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold text-{{ $color }}">
                                    {{ $room['room_name'] }}
                                </div>
                                <div class="text-muted small">
                                    {{ __('room.floor') }} :<span class="text-{{ $color }}">{{ $room['floor_name'] }}</span> |
                                    {{ __('room.location') }} :<span
                                        class="text-{{ $color }}">{{ $room['location']['location_name'] ?? '-' }}</span>
                                    |
                                    {{ __('room.type') }} :<span
                                        class="text-{{ $color }}">{{ $room['room_type']['type_name'] ?? '-' }}</span>
                                    {{ __('room.size') }} :<span
                                        class="text-{{ $color }}">{{ $room['room_type']['room_size'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ Default: Price & Status --}}
                        <div class="text-end action-default">
                            <div class="fw-semibold text-muted mb-1">
                                {{ __('titles.price') }}:
                                <span class="text-success">{{ $room['room_type']['price'] ?? '—' }}</span>
                            </div>
                            <span class="{{ $room['status_badge']['badge'] }}">
                                {{ __($room['status_badge']['name']) }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card text-center">
            <x-empty-state title="{{ __('titles.no_room_found') }}" message="{{ __('titles.please_find_another_location') }}"
                svg="svgs/no_result.svg" width="450px" />
        </div>
    @endforelse

    {{-- 📄 Pagination --}}
    @if ($rooms->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $rooms->links('pagination::bootstrap-5') }}
        </div>
    @endif

@endsection
