@extends('layouts.app')

@section('content')

    @php
        $colors = ['primary', 'success', 'warning', 'info', 'danger', 'purple', 'teal', 'orange'];
    @endphp

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
                        អាគារ <kbd
                            class="bg-{{ $color }}-lt border-{{ $color }} text-{{ $color }}">{{ $building }}</kbd>
                    </span>
                </div>
                <div class="text-{{ $color }} small fw-semibold">
                    {{ $groupedRooms->count() }} {{ __('titles.rooms') }}
                </div>
            </div>

            <div class="list-group list-group-flush">
                @foreach ($groupedRooms as $room)
                    <a href="{{ route('room.show', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 hover-shadow room-item"
                        style="transition: all 0.2s ease;">
                        {{-- ✅ Checkbox --}}
                        <div class="d-flex align-items-center flex-grow-1">
                            <input type="checkbox" class="form-check-input me-3 room-checkbox"
                                data-room-id="{{ $room['id'] }}" style="cursor:pointer;">

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
                                    {{ __('room.floor') }} :<span
                                        class="text-{{ $color }}">{{ $room['floor_name'] }}</span> |
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
                            <div class="fw-semibold text-muted">
                                {{ __('titles.price') }}:
                                <span class="text-dark">{{ $room['room_type']['price'] ?? '—' }}</span>
                            </div>
                            <span class="{{ $room['status_badge']['badge'] }}">
                                {{ __($room['status_badge']['name']) }}
                            </span>
                        </div>

                        {{-- ✅ When Checked: Edit & Delete buttons (use button instead of <a>) --}}
                        <div class="text-end action-buttons d-none">
                            <form action="{{ route('room.edit', ['room_id' => $room['id'], 'location_id' => $locationId]) }}"
                                method="GET" class="d-inline">
                                @csrf
                                <input type="hidden" name="color" value="{{ $color }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <x-icon name="edit" /> {{ __('titles.edit') }}
                                </button>
                            </form>

                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                data-bs-target="#{{ $room['id'] }}">
                                <x-icon name="trash" /> {{ __('titles.delete') }}
                            </button>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card text-center">
            <x-empty-state title="{{ __('titles.no_room_found') }}"
                message="{{ __('titles.please_find_another_location') }}" svg="svgs/no_result.svg" width="450px" />
        </div>
    @endforelse

    {{-- 📄 Pagination --}}
    @if ($rooms->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $rooms->links('pagination::bootstrap-5') }}
        </div>
    @endif


    @foreach ($rooms as $room)
        <div class="modal modal-blur fade" id="{{ $room['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <form action="{{ route('room.destroy', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="modal-title">{{ __('modal.confirm_title') }}</div>
                            <div>{{ __('modal.confirm_message_room') }} <span
                                    class="badge bg-{{ $color }}-lt">{{ $room['room_name'] }}</span> ?</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                                {{ __('modal.cancel') }}
                            </button>
                            <button type="submit" class="btn btn-danger">
                                {{ __('modal.confirm') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection

@push('scripts')
    {{-- ✅ JavaScript to toggle buttons --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.room-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const parent = this.closest('.room-item');
                    const defaultSection = parent.querySelector('.action-default');
                    const actionButtons = parent.querySelector('.action-buttons');

                    if (this.checked) {
                        // Show action buttons and hide default section
                        defaultSection.classList.add('d-none');
                        actionButtons.classList.remove('d-none');

                        // Add active/highlight class
                        parent.classList.add('active');
                    } else {
                        // Hide action buttons and show default section
                        defaultSection.classList.remove('d-none');
                        actionButtons.classList.add('d-none');

                        // Remove active/highlight class
                        parent.classList.remove('active');
                    }
                });
            });
        });
    </script>
@endpush
