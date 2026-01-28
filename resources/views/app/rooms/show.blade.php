@extends('layouts.app')

@section('content')

    <div class="row g-3">
        <!-- ===== Left Info (Room Overview) ===== -->
        <div class="col-lg-4">
            <div class="row g-3">
                <div class="col-12">
                    <div class="card h-100">
                        <div class="card-body text-center position-relative">

                            {{-- Room Icon --}}
                            <div class="avatar avatar-xl bg-primary-lt mb-3">
                                <x-icon name="door" />
                            </div>

                            {{-- ===== Room Title ===== --}}
                            <h3 class="fw-bold mb-2">{{ $room['room_name'] }}</h3>

                            {{-- ===== Status Edit Dropdown ===== --}}
                            <form action="{{ route('room.update-status', [$room['id'], $room['location']['id']]) }}"
                                method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')

                                <div class="dropdown d-inline-block mb-2">
                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <x-icon name="pencil" class="me-1" /> {{ __('room.edit_status') }}
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

                            {{-- Location Info --}}
                            <p class="text-muted small mb-2">
                                <x-icon name="building" class="me-1" />
                                {{ __('room.building') }}: {{ $room['building_name'] }},
                                {{ __('room.floor') }}: {{ $room['floor_name'] }}
                            </p>

                            <p class="text-muted">
                                <x-icon name="map-pin" class="me-1" />
                                {{ ucfirst($room['location']['location_name']) }}
                            </p>

                            <span class="{{ $roomstatus['badge'] }}">
                                {{ __($roomstatus['name']) }}
                            </span>
                        </div>

                        {{-- Room Price --}}
                        <div class="card-body text-center">
                            <div class="h1 text-success fw-bold mb-1">
                                {{ number_format($room['room_type']['price'], 2) }}(៛)
                            </div>
                            <span class="text-muted">/ {{ __('room.per_month') }}</span>
                        </div>

                        {{-- Type & Size --}}
                        <div class="card-body text-center">
                            <div class="text-muted mb-2">
                                <x-icon name="tag" class="me-1" /> {{ __('room.type') }}:
                                <strong>{{ $room['room_type']['type_name'] }}</strong>
                            </div>
                            <div class="text-muted">
                                <x-icon name="ruler" class="me-1" /> {{ __('room.size') }}:
                                <strong>{{ $room['room_type']['room_size'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== Edit/Delete Buttons ===== --}}
                @if (userRole() != 'user')
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <a href="{{ route('room.edit', ['room_id' => $room['id'], 'location_id' => $room['location_id']]) }}"
                                    class="btn btn-primary w-100">
                                    <x-icon name="edit" /> {{ __('titles.edit') }}
                                </a>
                            </div>
                            <div class="col-lg-6">
                                <button class="btn btn-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#{{ $room['id'] }}">
                                    <x-icon name="trash" /> {{ __('titles.delete') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ===== Right Info (Clients & Description) ===== -->
        <div class="col-lg-8">
            <div class="row g-3">
                @if ($room['invoices'])
                    {{-- ===== Latest Invoice ===== --}}
                    <div class="col-12">

                        @php
                            $latestInvoice = collect($room['invoices'])->sortByDesc('invoice_date')->first();
                            $status = \App\Enum\InvoiceStatus::getStatus($latestInvoice['status']);
                        @endphp

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold mb-0">{{ __('invoice.latest_invoice') }}</h5>
                            <a href="{{ route('invoice.user_index', $locationId) }}"
                                class="btn btn-outline-primary btn-sm">
                                {{ __('invoice.view_all') }}
                            </a>
                        </div>

                        <a href="{{ route('invoice.show', ['id' => $latestInvoice['id'], 'locationId' => $locationId]) }}"
                            class="text-decoration-none">
                            <div class="card card-sm card-link-pop position-relative hover-shadow">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-primary mb-1">
                                            {{ __('invoice.no') }}: {{ $latestInvoice['invoice_no'] }}
                                        </div>
                                        <div class="text-muted small">
                                            <x-icon name="calendar-week" class="me-1" width="16" />
                                            {{ __('invoice.date') }}:
                                            {{ \Carbon\Carbon::parse($latestInvoice['invoice_date'])->translatedFormat('d F Y') }}
                                        </div>
                                        <div class="text-muted small">
                                            <x-icon name="clock" class="me-1" width="16" />
                                            {{ __('invoice.due_date') }}:
                                            {{ \Carbon\Carbon::parse($latestInvoice['due_date'])->translatedFormat('d F Y') }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="fw-bold text-success mb-1">
                                            {{ number_format($latestInvoice['total'], 2) }}(៛)
                                        </div>
                                        <span class="{{ $status['badge'] }}">
                                            {{ __($status['name']) }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Optional: Breakdown --}}
                                <div class="card-body border-top text-muted small">
                                    <div>{{ __('invoice.room_fee') }}:
                                        <span class="text-primary">
                                            {{ number_format($latestInvoice['room_fee'], 2) }}(៛)
                                        </span>
                                    </div>
                                    <div>{{ __('invoice.electric') }}:
                                        <span class="text-primary">
                                            {{ number_format(($latestInvoice['new_electric'] - $latestInvoice['old_electric']) * $latestInvoice['electric_rate'], 2) }}(៛)
                                        </span>
                                    </div>
                                    <div>{{ __('invoice.water') }}:
                                        <span class="text-primary">
                                            {{ number_format(($latestInvoice['new_water'] - $latestInvoice['old_water']) * $latestInvoice['water_rate'], 2) }}(៛)
                                        </span>
                                    </div>
                                    <div>{{ __('invoice.other_charge') }}:
                                        <span class="text-primary">
                                            {{ number_format($latestInvoice['other_charge'], 2) }}(៛)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>

                    </div>
                @endif

                {{-- ===== Clients List ===== --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">
                                <x-icon name="users" class="me-2 text-primary" />
                                {{ __('room.clients') }}
                            </h5>

                            @if ($clients->isEmpty())
                                <x-empty-state title="{{ __('room.no_client_found') }}"
                                    message="{{ __('room.there_are_no_client_in_this_room') }}" svg="svgs/no_result.svg"
                                    width="200px" />
                            @else
                                <div class="row row-cards g-2">
                                    @foreach ($clients as $client)
                                        <div class="col-12">
                                            <div class="card card-sm position-relative">

                                                {{-- Rental Status Dot --}}
                                                @if ($client['nearly_end'])
                                                    <span
                                                        class="position-absolute top-0 end-0 translate-middle p-2 border border-light rounded-circle {{ $client['dot_color'] }}"
                                                        title="{{ $client['alert_message'] }}">
                                                    </span>
                                                @endif

                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">

                                                        {{-- Avatar --}}
                                                        @if (empty($client['client_image']))
                                                            <span
                                                                class="avatar me-3 bg-{{ $client['gender'] == 'ប្រុស' ? 'blue' : 'pink' }} text-white fw-bold">
                                                                {{ strtoupper(substr($client['username'], 0, 1)) }}
                                                            </span>
                                                        @else
                                                            <span class="avatar me-3"
                                                                style="background-image: url('{{ $client['image'] }}'); object-fit: cover;"></span>
                                                        @endif

                                                        <div class="flex-fill">
                                                            <div class="fw-bold text-primary">
                                                                {{ ucfirst($client['username']) }}
                                                            </div>
                                                            <div class="text-muted small">
                                                                <x-icon name="calendar-week" class="me-1"
                                                                    width="16" />
                                                                {{ __('client.date_of_birth') }}:
                                                                {{ $client['dateOfBirth'] }}
                                                            </div>
                                                            <div class="text-muted small mt-1">
                                                                <x-icon name="phone" class="me-1" width="16" />
                                                                {{ $client['phone_number'] }}
                                                                <span class="mx-2 text-dot"></span>
                                                                <x-icon name="map-pin" class="me-1" width="16" />
                                                                {{ $client['address'] }}
                                                            </div>
                                                        </div>

                                                        {{-- Rental Info --}}
                                                        <div class="d-flex flex-column align-items-end me-3">
                                                            <span class="{{ $client['clientstatus']['badge'] }}">
                                                                {{ __($client['clientstatus']['name']) }}
                                                            </span>

                                                            <div class="text-muted fs-6 mt-1">
                                                                {{ __('client.start_rental_date') }}:
                                                                {{ $client['start_rental_date'] }}
                                                            </div>

                                                            <div class="text-muted fs-6">
                                                                {{ __('client.end_rental_date') }}:
                                                                {{ $client['end_rental_date'] }}
                                                            </div>

                                                            {{-- Alert Message --}}
                                                            @if ($client['nearly_end'])
                                                                <div
                                                                    class="mt-2 small fw-bold text-{{ $client['dot_color'] == 'bg-danger' ? 'danger' : 'warning' }}">
                                                                    {{ $client['alert_message'] }}
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Dropdown Actions --}}
                                                        <div class="dropdown">
                                                            <a href="#" class="btn-action"
                                                                data-bs-toggle="dropdown">
                                                                <x-icon name="dots-vertical" />
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                {{-- <a href="#" class="dropdown-item">
                                                                    <x-icon name="eye" class="me-1" />
                                                                    {{ __('client.view_profile') }}
                                                                </a> --}}
                                                                <a href="#" data-bs-toggle="modal"
                                                                    data-bs-target="#{{ $client['id'] }}"
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

    {{-- ===== Delete Room Modal ===== --}}
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
                                class="badge bg-primary-lt">{{ $room['room_name'] }}</span> ?</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto"
                            data-bs-dismiss="modal">{{ __('modal.cancel') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('modal.confirm') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== End Lease Modal ===== --}}
    @foreach ($clients as $client)
        <div class="modal modal-blur fade" id="{{ $client['id'] }}" tabindex="-1" role="dialog" aria-hidden="true">
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
                                    'name' => '<span class="text-primary">' . $client['username'] . '</span>',
                                ]) !!}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-link link-secondary me-auto"
                                data-bs-dismiss="modal">{{ __('modal.cancel') }}</button>
                            <button type="submit" class="btn btn-danger">{{ __('modal.confirm') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
