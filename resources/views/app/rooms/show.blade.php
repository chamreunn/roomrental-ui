@extends('layouts.app')

@section('content')
    <div class="row g-3">

        {{-- ================= LEFT: ROOM OVERVIEW ================= --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">

                    {{-- Icon --}}
                    <div class="avatar avatar-xl bg-primary-lt mb-3">
                        <x-icon name="door" />
                    </div>

                    {{-- Title --}}
                    <div class="h2 fw-bold mb-1">{{ $room['room_name'] }}</div>

                    {{-- Status badge --}}
                    <span class="{{ $roomstatus['badge'] }} mb-3 d-inline-block">
                        {{ __($roomstatus['name']) }}
                    </span>

                    {{-- Location / Building / Floor --}}
                    <div class="text-muted small mt-2">
                        <div class="mb-1">
                            <x-icon name="building" class="me-1" />
                            {{ __('room.building') }}: <b>{{ $room['building_name'] }}</b>
                            <span class="mx-2 text-dot"></span>
                            {{ __('room.floor') }}: <b>{{ $room['floor_name'] }}</b>
                        </div>

                        <div>
                            <x-icon name="map-pin" class="me-1" />
                            <b>{{ ucfirst($room['location']['location_name'] ?? '-') }}</b>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Price --}}
                    <div class="mb-2">
                        <div class="text-muted small">{{ __('room.price') }}</div>
                        <div class="h1 text-success fw-bold mb-0">
                            {{ number_format((float) ($room['room_type']['price'] ?? 0), 2) }}(៛)
                        </div>
                        <div class="text-muted small">/ {{ __('room.per_month') }}</div>
                    </div>

                    <hr class="my-3">

                    {{-- Type & Size --}}
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-muted small">{{ __('room.type') }}</div>
                            <div class="fw-bold">{{ $room['room_type']['type_name'] ?? '-' }}</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">{{ __('room.size') }}</div>
                            <div class="fw-bold">{{ $room['room_type']['room_size'] ?? '-' }}</div>
                        </div>
                    </div>

                    <hr class="my-3">

                    {{-- Status Edit Dropdown --}}
                    <form action="{{ route('room.update-status', [$room['id'], $room['location']['id']]) }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('PATCH')

                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <x-icon name="pencil" class="me-1" />
                                {{ __('room.edit_status') }}
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                @foreach ($statuses as $key => $status)
                                    <li>
                                        <button type="submit" name="status" value="{{ $key }}"
                                            class="dropdown-item">
                                            <span class="{{ $status['text'] }}">{{ __($status['name']) }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </form>

                    {{-- Edit/Delete Buttons --}}
                    @if (userRole() != 'user')
                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <a href="{{ route('room.edit', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                                    class="btn btn-primary w-100">
                                    <x-icon name="edit" class="me-1" /> {{ __('titles.edit') }}
                                </a>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#delete-room-{{ $room['id'] }}">
                                    <x-icon name="trash" class="me-1" /> {{ __('titles.delete') }}
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- ================= RIGHT: INVOICE + CLIENTS ================= --}}
        <div class="col-lg-8">
            <div class="row g-3">

                {{-- ===== Latest Invoice (from all clients invoices) ===== --}}
                @if (!empty($latestInvoice))
                    @php
                        $invStatus = $latestInvoiceStatus;
                    @endphp

                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold">{{ __('invoice.latest_invoice') }}</h4>
                            <a href="{{ route('invoice.user_index', $locationId) }}"
                                class="btn btn-outline-primary btn-sm">
                                {{ __('invoice.view_all') }}
                            </a>
                        </div>

                        <a href="{{ route('invoice.show', ['id' => $latestInvoice['id'], 'locationId' => $locationId]) }}"
                            class="text-decoration-none">
                            <div class="card mt-2 card-link-pop">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-primary mb-1">
                                            {{ __('invoice.no') }}: {{ $latestInvoice['invoice_no'] ?? '-' }}
                                        </div>

                                        <div class="text-muted small">
                                            <x-icon name="calendar-week" class="me-1" width="16" />
                                            {{ __('invoice.date') }}:
                                            {{ !empty($latestInvoice['invoice_date']) ? \Carbon\Carbon::parse($latestInvoice['invoice_date'])->translatedFormat('d F Y') : '-' }}
                                        </div>

                                        <div class="text-muted small">
                                            <x-icon name="clock" class="me-1" width="16" />
                                            {{ __('invoice.due_date') }}:
                                            {{ !empty($latestInvoice['due_date']) ? \Carbon\Carbon::parse($latestInvoice['due_date'])->translatedFormat('d F Y') : '-' }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="h3 fw-bold text-success mb-1">
                                            {{ number_format((float) ($latestInvoice['total'] ?? 0), 2) }}(៛)
                                        </div>
                                        @if ($invStatus)
                                            <span class="{{ $invStatus['badge'] }}">{{ __($invStatus['name']) }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Breakdown (optional) --}}
                                <div class="card-body border-top text-muted small">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.room_fee') }}</span>
                                        <b
                                            class="text-primary">{{ number_format((float) ($latestInvoice['room_fee'] ?? 0), 2) }}(៛)</b>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.electric') }}</span>
                                        @php
                                            $electric =
                                                ((float) ($latestInvoice['new_electric'] ?? 0) -
                                                    (float) ($latestInvoice['old_electric'] ?? 0)) *
                                                (float) ($latestInvoice['electric_rate'] ?? 0);
                                        @endphp
                                        <b class="text-primary">{{ number_format($electric, 2) }}(៛)</b>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.water') }}</span>
                                        @php
                                            $water =
                                                ((float) ($latestInvoice['new_water'] ?? 0) -
                                                    (float) ($latestInvoice['old_water'] ?? 0)) *
                                                (float) ($latestInvoice['water_rate'] ?? 0);
                                        @endphp
                                        <b class="text-primary">{{ number_format($water, 2) }}(៛)</b>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.other_charge') }}</span>
                                        <b
                                            class="text-primary">{{ number_format((float) ($latestInvoice['other_charge'] ?? 0), 2) }}(៛)</b>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- ===== Clients ===== --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="fw-bold mb-0">
                                    <x-icon name="users" class="me-2 text-primary" />
                                    {{ __('room.clients') }}
                                </h4>

                                <div class="text-muted small">
                                    {{ __('room.total') }}: <b>{{ $clients->count() }}</b>
                                </div>
                            </div>

                            @if ($clients->isEmpty())
                                <x-empty-state title="{{ __('room.no_client_found') }}"
                                    message="{{ __('room.there_are_no_client_in_this_room') }}" svg="svgs/no_result.svg"
                                    width="200px" />
                            @else
                                <div class="row row-cards g-2">
                                    @foreach ($clients as $client)
                                        <div class="col-12">
                                            <div class="card card-sm position-relative">

                                                {{-- Status dot --}}
                                                @if ($client['nearly_end'])
                                                    <span
                                                        class="position-absolute top-0 end-0 translate-middle p-2 border border-light rounded-circle {{ $client['dot_color'] }}"
                                                        title="{{ $client['alert_message'] }}">
                                                    </span>
                                                @endif

                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">

                                                        {{-- Avatar --}}
                                                        <span class="avatar avatar-md me-3"
                                                            style="background-image: url('{{ $client['client_image_url'] }}');">
                                                        </span>

                                                        {{-- Client info --}}
                                                        <div class="flex-fill">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <div class="fw-bold text-primary">
                                                                    {{ ucfirst($client['username'] ?? '-') }}
                                                                </div>

                                                                <span
                                                                    class="{{ $client['clientstatus']['badge'] ?? 'badge bg-secondary-lt' }}">
                                                                    {{ __($client['clientstatus']['name'] ?? '-') }}
                                                                </span>
                                                            </div>

                                                            <div class="text-muted small mt-1">
                                                                <x-icon name="calendar-week" class="me-1"
                                                                    width="16" />
                                                                {{ __('client.date_of_birth') }}:
                                                                {{ $client['dob_text'] }}
                                                            </div>

                                                            <div class="text-muted small mt-1">
                                                                <x-icon name="phone" class="me-1" width="16" />
                                                                {{ $client['phone_number'] ?? '-' }}
                                                                <span class="mx-2 text-dot"></span>
                                                                <x-icon name="map-pin" class="me-1" width="16" />
                                                                {{ $client['address'] ?? '-' }}
                                                            </div>

                                                            @if (!empty($client['alert_message']))
                                                                <div
                                                                    class="small fw-bold mt-2
                                                                @if ($client['dot_color'] == 'bg-danger') text-danger
                                                                @elseif($client['dot_color'] == 'bg-warning') text-warning
                                                                @else text-success @endif">
                                                                    {{ $client['alert_message'] }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Rental dates --}}
                                                        <div class="text-end me-3 d-none d-md-block">
                                                            <div class="text-muted small">
                                                                {{ __('client.start_rental_date') }}</div>
                                                            <div class="fw-bold">{{ $client['start_text'] }}</div>

                                                            <div class="text-muted small mt-2">
                                                                {{ __('client.end_rental_date') }}</div>
                                                            <div class="fw-bold">{{ $client['end_text'] }}</div>
                                                        </div>

                                                        {{-- Actions --}}
                                                        <div class="dropdown">
                                                            <a href="#" class="btn btn-outline-secondary btn-sm"
                                                                data-bs-toggle="dropdown">
                                                                <x-icon name="dots-vertical" />
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                <a href="#" data-bs-toggle="modal"
                                                                    data-bs-target="#end-lease-{{ $client['id'] }}"
                                                                    class="dropdown-item text-danger">
                                                                    <x-icon name="xbox-x" class="me-1" />
                                                                    {{ __('client.end_lease') }}
                                                                </a>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ================= Delete Room Modal ================= --}}
    <div class="modal modal-blur fade" id="delete-room-{{ $room['id'] }}" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <form action="{{ route('room.destroy', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-title">{{ __('modal.confirm_title') }}</div>
                        <div>
                            {{ __('modal.confirm_message_room') }}
                            <span class="badge bg-primary-lt">{{ $room['room_name'] }}</span> ?
                        </div>
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

    {{-- ================= End Lease Modals ================= --}}
    @foreach ($clients as $client)
        <div class="modal modal-blur fade" id="end-lease-{{ $client['id'] }}" tabindex="-1" role="dialog"
            aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                <form action="{{ route('clients.update-client-status', [$client['id'], $inactive, $locationId]) }}"
                    method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="modal-title">{{ __('modal.confirm_client_title') }}</div>
                            <div>
                                {!! __('modal.confirm_client_message', [
                                    'action' => '<span class="badge bg-danger-lt">' . __('client.end_lease') . '</span>',
                                    'name' => '<span class="text-primary">' . ($client['username'] ?? '-') . '</span>',
                                ]) !!}
                            </div>
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
