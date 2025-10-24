@extends('layouts.app')

@section('content')

    {{-- 🧱 Group by Building --}}
    @forelse ($rooms->groupBy('building_name') as $building => $groupedRooms)
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                <i class="ti ti-building me-1"></i> {{ $building }}
            </div>
            <div class="list-group list-group-flush">
                @foreach ($groupedRooms as $room)
                    <a href="#"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 hover-shadow"
                        style="transition: all 0.2s ease;">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="avatar bg-primary-lt text-primary fw-bold">
                                    {{ strtoupper(substr($room['room_name'], 0, 1)) }}
                                </span>
                            </div>
                            <div>
                                <div class="fw-bold text-primary">
                                    {{ $room['room_name'] }}
                                </div>
                                <div class="text-muted small">
                                    <x-icon name="stairs-up" class="icon-2" />{{ $room['floor_name'] }} | <x-icon name="map-pin" class="icon-2" />{{ $room['location']['location_name'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-semibold text-muted">
                                {{ __('titles.price') }}:
                                <span class="text-dark">{{ $room['room_type']['price'] ?? '—' }}</span>
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
        <div class="alert alert-warning text-center">
            {{ __('messages.no_data_found') }}
        </div>
    @endforelse

    {{-- 📄 Pagination --}}
    @if ($rooms->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $rooms->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
