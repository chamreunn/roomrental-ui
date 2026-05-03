@extends('layouts.app')

@section('content')
    <div class="row g-3">

        {{-- Room overview --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl bg-primary-lt text-primary mb-3">
                        <x-icon name="door" />
                    </div>

                    <div class="h2 fw-bold mb-1">
                        {{ $room['room_name'] ?? '-' }}
                    </div>

                    <span class="{{ $roomstatus['badge'] ?? 'badge bg-secondary-lt text-secondary' }} mb-3 d-inline-block">
                        {{ __($roomstatus['name'] ?? '-') }}
                    </span>

                    <div class="text-secondary small mt-2">
                        <div class="mb-1">
                            <x-icon name="building" class="me-1" />
                            {{ __('room.building') }}:
                            <b>{{ $room['building_name'] ?? '-' }}</b>

                            <span class="mx-2 text-dot"></span>

                            {{ __('room.floor') }}:
                            <b>{{ $room['floor_name'] ?? '-' }}</b>
                        </div>

                        <div>
                            <x-icon name="map-pin" class="me-1" />
                            <b>{{ $room['location']['location_name'] ?? '-' }}</b>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="mb-2">
                        <div class="text-secondary small">{{ __('room.price') }}</div>
                        <div class="h1 text-success fw-bold mb-0">
                            {{ number_format((float) ($room['room_type']['price'] ?? 0), 2) }}(៛)
                        </div>
                        <div class="text-secondary small">
                            / {{ __('room.per_month') }}
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="text-secondary small">{{ __('room.type') }}</div>
                            <div class="fw-bold">
                                {{ $room['room_type']['type_name'] ?? '-' }}
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="text-secondary small">{{ __('room.size') }}</div>
                            <div class="fw-bold">
                                {{ $room['room_type']['room_size'] ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <form action="{{ route('room.update-status', [$room['id'], $locationId]) }}" method="POST"
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
                                            <span class="{{ $status['text'] ?? '' }}">
                                                {{ __($status['name'] ?? '-') }}
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </form>

                    @if (userRole() != 'user')
                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <a href="{{ route('room.edit', ['room_id' => $room['id'], 'location_id' => $locationId]) }}"
                                    class="btn btn-primary w-100">
                                    <x-icon name="edit" class="me-1" />
                                    {{ __('titles.edit') }}
                                </a>
                            </div>

                            <div class="col-6">
                                <button class="btn btn-danger w-100" data-bs-toggle="modal"
                                    data-bs-target="#delete-room-{{ $room['id'] }}">
                                    <x-icon name="trash" class="me-1" />
                                    {{ __('titles.delete') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Client stats --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <x-icon name="chart-bar" />
                        {{ __('Client Summary') }}
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="card card-sm bg-primary-lt">
                                <div class="card-body">
                                    <div class="text-secondary small">{{ __('Clients') }}</div>
                                    <div class="h2 mb-0">{{ $clientStats['total'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card card-sm bg-purple-lt">
                                <div class="card-body">
                                    <div class="text-secondary small">{{ __('Sub Tenants') }}</div>
                                    <div class="h2 mb-0">{{ $clientStats['subclients'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card card-sm bg-warning-lt">
                                <div class="card-body">
                                    <div class="text-secondary small">{{ __('Nearly End') }}</div>
                                    <div class="h2 mb-0">{{ $clientStats['nearly_end'] }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card card-sm bg-success-lt">
                                <div class="card-body">
                                    <div class="text-secondary small">{{ __('Invoices') }}</div>
                                    <div class="h2 mb-0">{{ $clientStats['invoices'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right content --}}
        <div class="col-lg-8">
            <div class="row g-3">

                {{-- Latest invoice --}}
                @if (!empty($latestInvoice))
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0 fw-bold">
                                <x-icon name="receipt" class="me-1 text-primary" />
                                {{ __('invoice.latest_invoice') }}
                            </h4>

                            <a href="{{ route('invoice.user_index', $locationId) }}"
                                class="btn btn-outline-primary btn-sm">
                                {{ __('invoice.view_all') }}
                            </a>
                        </div>

                        <a href="{{ $latestInvoice['show_url'] ?? '#' }}" class="text-decoration-none">
                            <div class="card mt-2 card-link-pop">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-primary mb-1">
                                            {{ __('invoice.no') }}:
                                            {{ $latestInvoice['invoice_no'] ?? '-' }}
                                        </div>

                                        <div class="text-secondary small">
                                            <x-icon name="calendar-week" class="me-1" width="16" />
                                            {{ __('invoice.date') }}:
                                            {{ $latestInvoice['invoice_date_text'] ?? '-' }}
                                        </div>

                                        <div class="text-secondary small">
                                            <x-icon name="clock" class="me-1" width="16" />
                                            {{ __('invoice.due_date') }}:
                                            {{ $latestInvoice['due_date_text'] ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <div class="h3 fw-bold text-success mb-1">
                                            {{ $latestInvoice['total_text'] ?? '0.00(៛)' }}
                                        </div>

                                        @if ($latestInvoiceStatus)
                                            <span class="{{ $latestInvoiceStatus['badge'] ?? 'badge bg-secondary-lt' }}">
                                                {{ __($latestInvoiceStatus['name'] ?? '-') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-body border-top text-secondary small">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.room_fee') }}</span>
                                        <b class="text-primary">{{ $latestInvoice['room_fee_text'] ?? '0.00(៛)' }}</b>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.electric') }}</span>
                                        <b
                                            class="text-primary">{{ $latestInvoice['electric_total_text'] ?? '0.00(៛)' }}</b>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.water') }}</span>
                                        <b class="text-primary">{{ $latestInvoice['water_total_text'] ?? '0.00(៛)' }}</b>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <span>{{ __('invoice.other_charge') }}</span>
                                        <b class="text-primary">{{ $latestInvoice['other_charge_text'] ?? '0.00(៛)' }}</b>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                {{-- Clients --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <x-icon name="users" class="me-2 text-primary" />
                                    {{ __('room.clients') }}
                                </h3>
                                <div class="text-secondary small">
                                    {{ __('Full tenant information, sub tenants, documents and invoices.') }}
                                </div>
                            </div>

                            <div class="card-actions">
                                <span class="badge bg-primary-lt text-primary">
                                    {{ __('room.total') }}: {{ $clients->count() }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($clients->isEmpty())
                                <x-empty-state title="{{ __('room.no_client_found') }}"
                                    message="{{ __('room.there_are_no_client_in_this_room') }}" svg="svgs/no_result.svg"
                                    width="200px" />
                            @else
                                <div class="accordion" id="roomClientsAccordion">
                                    @foreach ($clients as $client)
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="client-heading-{{ $client['id'] }}">
                                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}"
                                                    type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#client-collapse-{{ $client['id'] }}"
                                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                                    <div class="d-flex align-items-center w-100 me-3">
                                                        <span class="avatar avatar-md me-3">
                                                            <img src="{{ $client['client_image_url'] }}"
                                                                alt="{{ $client['username'] ?? '-' }}">
                                                        </span>

                                                        <div class="flex-fill text-start">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="fw-bold text-primary">
                                                                    {{ $client['username'] ?? '-' }}
                                                                </span>

                                                                <span
                                                                    class="{{ $client['clientstatus']['badge'] ?? 'badge bg-secondary-lt' }}">
                                                                    {{ __($client['clientstatus']['name'] ?? '-') }}
                                                                </span>

                                                                @if ($client['nearly_end'])
                                                                    <span
                                                                        class="status-dot status-dot-animated {{ $client['dot_color'] }}"></span>
                                                                @endif
                                                            </div>

                                                            <div class="text-secondary small">
                                                                {{ $client['phone_number'] ?? '-' }}
                                                                <span class="mx-2 text-dot"></span>
                                                                {{ $client['alert_message'] }}
                                                            </div>
                                                        </div>

                                                        <div class="text-end d-none d-md-block">
                                                            <div class="text-secondary small">
                                                                {{ __('client.end_rental_date') }}
                                                            </div>
                                                            <div class="fw-bold">
                                                                {{ $client['end_text'] }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </button>
                                            </h2>

                                            <div id="client-collapse-{{ $client['id'] }}"
                                                class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                                data-bs-parent="#roomClientsAccordion">
                                                <div class="accordion-body">

                                                    {{-- Main client details --}}
                                                    <div class="row g-3">
                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.date_of_birth') }}</div>
                                                                    <div class="fw-bold">{{ $client['dob_text'] }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.gender') }}</div>
                                                                    <div class="fw-bold">{{ $client['gender_text'] }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.email') }}</div>
                                                                    <div class="fw-bold text-truncate">
                                                                        {{ $client['email'] ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.phone_number') }}</div>
                                                                    <div class="fw-bold">
                                                                        {{ $client['phone_number'] ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.national_id') }}</div>
                                                                    <div class="fw-bold">
                                                                        {{ $client['national_id'] ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-xl-4">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.passport') }}</div>
                                                                    <div class="fw-bold">{{ $client['passport'] ?? '-' }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.start_rental_date') }}</div>
                                                                    <div class="fw-bold">{{ $client['start_text'] }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <div class="card card-sm">
                                                                <div class="card-body">
                                                                    <div class="text-secondary small">
                                                                        {{ __('client.end_rental_date') }}</div>
                                                                    <div class="fw-bold">{{ $client['end_text'] }}</div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-12">
                                                            <div
                                                                class="alert {{ $client['is_expired'] ? 'alert-danger' : ($client['nearly_end'] ? 'alert-warning' : 'alert-success') }} mb-0">
                                                                <div class="d-flex">
                                                                    <div>
                                                                        <span
                                                                            class="status-dot {{ $client['dot_color'] }}"></span>
                                                                    </div>
                                                                    <div class="ms-2">
                                                                        <div class="fw-bold">
                                                                            {{ $client['alert_message'] }}</div>
                                                                        <div class="small">
                                                                            {{ __('client.address') }}:
                                                                            {{ $client['address'] ?? '-' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Sub tenants --}}
                                                    <div class="card mt-3">
                                                        <div class="card-header">
                                                            <div>
                                                                <h4 class="card-title">
                                                                    <x-icon name="users-plus" />
                                                                    {{ __('Sub Tenants') }}
                                                                </h4>
                                                                <div class="text-secondary small">
                                                                    {{ count($client['subclients_formatted']) }}
                                                                    {{ __('people') }}
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="card-body">
                                                            @if (empty($client['subclients_formatted']))
                                                                <div class="empty">
                                                                    <div class="empty-icon">
                                                                        <x-icon name="users" />
                                                                    </div>
                                                                    <p class="empty-title">
                                                                        {{ __('No sub tenants') }}
                                                                    </p>
                                                                </div>
                                                            @else
                                                                <div class="row g-2">
                                                                    @foreach ($client['subclients_formatted'] as $subclient)
                                                                        <div class="col-md-6">
                                                                            <div class="card card-sm">
                                                                                <div class="card-body">
                                                                                    <div class="d-flex align-items-start">
                                                                                        <span
                                                                                            class="avatar avatar-md me-3">
                                                                                            <img src="{{ $subclient['sub_client_image_url'] }}"
                                                                                                alt="{{ $subclient['username'] }}">
                                                                                        </span>

                                                                                        <div class="flex-fill">
                                                                                            <div
                                                                                                class="fw-bold text-primary">
                                                                                                {{ $subclient['username'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small">
                                                                                                {{ __('client.date_of_birth') }}:
                                                                                                {{ $subclient['dob_text'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small">
                                                                                                {{ __('client.gender') }}:
                                                                                                {{ $subclient['gender_text'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small">
                                                                                                {{ __('client.phone_number') }}:
                                                                                                {{ $subclient['phone_number'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small text-truncate">
                                                                                                {{ __('client.email') }}:
                                                                                                {{ $subclient['email'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small">
                                                                                                {{ __('client.national_id') }}:
                                                                                                {{ $subclient['national_id'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small">
                                                                                                {{ __('client.passport') }}:
                                                                                                {{ $subclient['passport'] }}
                                                                                            </div>

                                                                                            <div
                                                                                                class="text-secondary small mt-1">
                                                                                                {{ __('client.address') }}:
                                                                                                {{ $subclient['address'] }}
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

                                                    {{-- Documents --}}
                                                    <div class="card mt-3">
                                                        <div class="card-header">
                                                            <h4 class="card-title">
                                                                <x-icon name="files" />
                                                                {{ __('Documents') }}
                                                            </h4>
                                                        </div>

                                                        <div class="card-body">
                                                            @if (empty($client['documents_formatted']))
                                                                <div class="empty">
                                                                    <div class="empty-icon">
                                                                        <x-icon name="file-off" />
                                                                    </div>
                                                                    <p class="empty-title">{{ __('No documents') }}</p>
                                                                </div>
                                                            @else
                                                                <div class="list-group list-group-flush">
                                                                    @foreach ($client['documents_formatted'] as $document)
                                                                        <div class="list-group-item px-0">
                                                                            <div class="row align-items-center">
                                                                                <div class="col-auto">
                                                                                    <span
                                                                                        class="avatar bg-primary-lt text-primary">
                                                                                        <x-icon name="file" />
                                                                                    </span>
                                                                                </div>

                                                                                <div class="col text-truncate">
                                                                                    <div class="fw-semibold text-truncate">
                                                                                        {{ $document['file_name'] }}
                                                                                    </div>
                                                                                    <div class="text-secondary small">
                                                                                        {{ __('Client document') }}
                                                                                    </div>
                                                                                </div>

                                                                                <div class="col-auto">
                                                                                    @if ($document['view_url'])
                                                                                        <a href="{{ $document['view_url'] }}"
                                                                                            target="_blank"
                                                                                            class="btn btn-sm btn-outline-primary">
                                                                                            <x-icon name="eye" />
                                                                                            {{ __('View') }}
                                                                                        </a>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Invoices --}}
                                                    <div class="card mt-3">
                                                        <div class="card-header">
                                                            <h4 class="card-title">
                                                                <x-icon name="receipt" />
                                                                {{ __('Invoices') }}
                                                            </h4>
                                                        </div>

                                                        <div class="card-body">
                                                            @if (empty($client['invoices_formatted']))
                                                                <div class="empty">
                                                                    <div class="empty-icon">
                                                                        <x-icon name="receipt-off" />
                                                                    </div>
                                                                    <p class="empty-title">{{ __('No invoices') }}</p>
                                                                </div>
                                                            @else
                                                                <div class="table-responsive">
                                                                    <table class="table table-vcenter">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>{{ __('invoice.no') }}</th>
                                                                                <th>{{ __('invoice.date') }}</th>
                                                                                <th>{{ __('invoice.due_date') }}</th>
                                                                                <th>{{ __('invoice.room_fee') }}</th>
                                                                                <th>{{ __('invoice.electric') }}</th>
                                                                                <th>{{ __('invoice.water') }}</th>
                                                                                <th>{{ __('invoice.total') }}</th>
                                                                                <th>{{ __('status.status') }}</th>
                                                                                <th class="w-1"></th>
                                                                            </tr>
                                                                        </thead>

                                                                        <tbody>
                                                                            @foreach ($client['invoices_formatted'] as $invoice)
                                                                                <tr>
                                                                                    <td class="fw-semibold">
                                                                                        {{ $invoice['invoice_no'] ?? '-' }}
                                                                                    </td>
                                                                                    <td>{{ $invoice['invoice_date_text'] }}
                                                                                    </td>
                                                                                    <td>{{ $invoice['due_date_text'] }}
                                                                                    </td>
                                                                                    <td>{{ $invoice['room_fee_text'] }}
                                                                                    </td>
                                                                                    <td>{{ $invoice['electric_total_text'] }}
                                                                                    </td>
                                                                                    <td>{{ $invoice['water_total_text'] }}
                                                                                    </td>
                                                                                    <td class="fw-bold text-success">
                                                                                        {{ $invoice['total_text'] }}
                                                                                    </td>
                                                                                    <td>
                                                                                        @if ($invoice['status_meta'])
                                                                                            <span
                                                                                                class="{{ $invoice['status_meta']['badge'] ?? 'badge bg-secondary-lt' }}">
                                                                                                {{ __($invoice['status_meta']['name'] ?? '-') }}
                                                                                            </span>
                                                                                        @endif
                                                                                    </td>
                                                                                    <td>
                                                                                        @if ($invoice['show_url'])
                                                                                            <a href="{{ $invoice['show_url'] }}"
                                                                                                class="btn btn-sm btn-outline-primary">
                                                                                                {{ __('View') }}
                                                                                            </a>
                                                                                        @endif
                                                                                    </td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Actions --}}
                                                    <div class="btn-list justify-content-end mt-3">
                                                        @if ($client['edit_url'])
                                                            <a href="{{ $client['edit_url'] }}"
                                                                class="btn btn-outline-primary">
                                                                <x-icon name="edit" />
                                                                {{ __('titles.edit') }}
                                                            </a>
                                                        @endif

                                                        <button type="button" class="btn btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#end-lease-{{ $client['id'] }}">
                                                            <x-icon name="xbox-x" />
                                                            {{ __('client.end_lease') }}
                                                        </button>
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

    {{-- Delete room modal --}}
    <div class="modal modal-blur fade" id="delete-room-{{ $room['id'] }}" tabindex="-1" role="dialog"
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <form action="{{ route('room.destroy', ['room_id' => $room['id'], 'location_id' => $locationId]) }}"
                method="POST">
                @csrf
                @method('DELETE')

                <div class="modal-content">
                    <div class="modal-body">
                        <div class="modal-title">{{ __('modal.confirm_title') }}</div>
                        <div>
                            {{ __('modal.confirm_message_room') }}
                            <span class="badge bg-primary-lt">{{ $room['room_name'] ?? '-' }}</span>?
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

    {{-- End lease modals --}}
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
                            <div class="modal-title">
                                {{ __('modal.confirm_client_title') }}
                            </div>

                            <div>
                                {!! __('modal.confirm_client_message', [
                                    'action' => '<span class="badge bg-danger-lt">' . __('client.end_lease') . '</span>',
                                    'name' => '<span class="text-primary">' . e($client['username'] ?? '-') . '</span>',
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
