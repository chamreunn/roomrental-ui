@extends('layouts.app')

@section('content')

    @forelse ($rooms->groupBy('building_name') as $index => $group)
        @php
            $building = $index;
            $groupedRooms = $group;
            $color = $colors[$loop->index % count($colors)];
        @endphp

        <div class="d-flex justify-content-between align-items-center mb-3">
            <form id="multi-delete-form" action="{{ route('room.multi_destroy', ['location_id' => $locationId]) }}"
                method="POST" class="d-none">
                @csrf
                @method('DELETE')
                <input type="hidden" name="room_ids" id="selected-room-ids">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#confirmMultiDeleteModal">
                    <x-icon name="trash" />
                    {{ __('titles.delete') }} <div id="selected-count" class="mx-2">0</div>
                </button>
            </form>
        </div>

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
                            <div class="fw-semibold text-muted mb-1">
                                {{ __('titles.price') }}:
                                <span class="text-success">{{ $room['room_type']['price'] ?? '—' }}</span>
                            </div>
                            <span class="{{ $room['status_badge']['badge'] }}">
                                {{ __($room['status_badge']['name']) }}
                            </span>
                        </div>

                        {{-- ✅ When Checked: Edit & Delete buttons (use button instead of <a>) --}}
                        <div class="text-end action-buttons d-none">
                            <form
                                action="{{ route('room.edit', ['room_id' => $room['id'], 'location_id' => $locationId]) }}"
                                method="GET" class="d-inline">
                                @csrf
                                <input type="hidden" name="color" value="{{ $color }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <x-icon name="edit" /> {{ __('titles.edit') }}
                                </button>
                            </form>
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

    <div class="modal modal-blur fade" id="confirmMultiDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="modal-title">{{ __('modal.confirm_title') }}</div>
                    {{ __('modal.confirm_message_multi_delete') }}
                    <strong><span id="modal-selected-count">0</span></strong> {{ __('titles.rooms') }}?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">
                        {{ __('modal.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirm-multi-delete-btn">
                        {{ __('modal.confirm') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.room-checkbox');
            const multiDeleteForm = document.getElementById('multi-delete-form');
            const selectedIdsInput = document.getElementById('selected-room-ids');
            const selectedCount = document.getElementById('selected-count');
            const modalSelectedCount = document.getElementById('modal-selected-count');
            const confirmBtn = document.getElementById('confirm-multi-delete-btn');

            function updateSelectedRooms() {
                const selected = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.dataset.roomId);

                if (selected.length > 0) {
                    multiDeleteForm.classList.remove('d-none');
                    selectedIdsInput.value = selected.join(',');
                    selectedCount.textContent = selected.length;
                    modalSelectedCount.textContent = selected.length;
                } else {
                    multiDeleteForm.classList.add('d-none');
                    selectedCount.textContent = 0;
                    modalSelectedCount.textContent = 0;
                }
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const parent = this.closest('.room-item');
                    const defaultSection = parent.querySelector('.action-default');
                    const actionButtons = parent.querySelector('.action-buttons');

                    if (this.checked) {
                        defaultSection.classList.add('d-none');
                        actionButtons.classList.remove('d-none');
                        parent.classList.add('active');
                    } else {
                        defaultSection.classList.remove('d-none');
                        actionButtons.classList.add('d-none');
                        parent.classList.remove('active');
                    }

                    updateSelectedRooms();
                });
            });

            // Confirm modal button click
            confirmBtn.addEventListener('click', function() {
                multiDeleteForm.submit();
            });
        });
    </script>
@endpush
