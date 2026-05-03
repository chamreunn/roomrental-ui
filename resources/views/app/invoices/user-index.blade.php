@extends('layouts.app')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Invoice Management') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Invoices') }}
                    </h2>
                    <div class="text-secondary mt-1">
                        {{ __('Review invoice payments, utility usage, and export selected invoices.') }}
                    </div>
                </div>

                <div class="col-auto ms-auto">
                    <a href="{{ route('invoice.user_create_invoice', $locationId) }}" class="btn btn-primary">
                        <x-icon name="plus" />
                        {{ __('invoice.create_invoice') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">

            <div class="row row-cards mb-3">
                @foreach ($summaryCards as $card)
                    <div class="col-sm-6 col-lg">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar {{ $card['class'] }}">
                                            <x-icon :name="$card['icon']" />
                                        </span>
                                    </div>
                                    <div class="col">
                                        <div class="text-secondary text-truncate">
                                            {{ $card['label'] }}
                                        </div>
                                        <div class="h2 mb-0 text-truncate">
                                            {{ $card['value'] }}
                                        </div>
                                        <div class="text-secondary small text-truncate">
                                            {{ $card['subtext'] }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">
                            <x-icon name="filter" />
                            {{ __('Filter Invoices') }}
                        </h3>
                        <div class="text-secondary small">
                            {{ __('Search by invoice number, room, building, floor, status, month or date range.') }}
                        </div>
                    </div>

                    @if ($hasFilters)
                        <div class="card-actions">
                            <a href="{{ route('invoice.user_index', $locationId) }}"
                                class="btn btn-outline-secondary btn-sm">
                                <x-icon name="rotate-clockwise" />
                                {{ __('invoice.reset') ?? 'Reset' }}
                            </a>
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('invoice.user_index', $locationId) }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-lg-3">
                                <label class="form-label">{{ __('Search') }}</label>
                                <div class="input-icon">
                                    <span class="input-icon-addon">
                                        <x-icon name="search" />
                                    </span>
                                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                                        placeholder="{{ __('invoice.search_placeholder') ?? 'Invoice No / Room' }}">
                                </div>
                            </div>

                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">{{ __('invoice.status') }}</label>
                                <select name="status" class="form-select tom-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($statuses as $key => $status)
                                        <option value="{{ $key }}" @selected((string) $filter_status === (string) $key)>
                                            {{ __($status['name']) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">{{ __('invoice.room_type') }}</label>
                                <select name="room_type" class="form-select tom-select">
                                    <option value="">{{ __('All') }}</option>
                                    @foreach ($roomTypes as $type)
                                        <option value="{{ $type['id'] }}" @selected((string) $filter_room_type === (string) $type['id'])>
                                            {{ $type['type_name'] ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">{{ __('invoice.month') }}</label>
                                <input type="month" name="month" class="form-control monthpicker" placeholder="{{ __('invoice.month') }}" value="{{ $filter_month }}"
                                    autocomplete="off">
                            </div>

                            <div class="col-6 col-md-4 col-lg-1">
                                <label class="form-label">{{ __('invoice.building') }}</label>
                                <input type="text" name="building_name" class="form-control"
                                    value="{{ $filter_building }}" placeholder="{{ __('invoice.building') }}">
                            </div>

                            <div class="col-6 col-md-4 col-lg-1">
                                <label class="form-label">{{ __('invoice.floor') }}</label>
                                <input type="text" name="floor_name" class="form-control" value="{{ $filter_floor }}"
                                    placeholder="{{ __('invoice.floor') }}">
                            </div>

                            <div class="col-6 col-md-4 col-lg-1">
                                <label class="form-label">{{ __('invoice.room') }}</label>
                                <input type="text" name="room_name" class="form-control" value="{{ $filter_room }}"
                                    placeholder="{{ __('invoice.room') }}">
                            </div>

                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">{{ __('invoice.from_date') }}</label>
                                <input type="date" name="from_date" class="form-control datepicker" placeholder="{{ __('invoice.from_date') }}" value="{{ $from_date }}"
                                    autocomplete="off">
                            </div>

                            <div class="col-6 col-md-4 col-lg-2">
                                <label class="form-label">{{ __('invoice.to_date') }}</label>
                                <input type="date" name="to_date" class="form-control datepicker" placeholder="{{ __('invoice.to_date') }}" value="{{ $to_date }}"
                                    autocomplete="off">
                            </div>

                            <div class="col-6 col-md-auto">
                                <button type="submit" class="btn btn-primary w-100">
                                    <x-icon name="search" />
                                    {{ __('invoice.filter') ?? 'Filter' }}
                                </button>
                            </div>

                            <div class="col-6 col-md-auto">
                                <a href="{{ route('invoice.user_index', $locationId) }}"
                                    class="btn btn-outline-secondary w-100">
                                    <x-icon name="rotate-clockwise" />
                                    {{ __('invoice.reset') ?? 'Reset' }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if (!empty($invoices) && count($invoices) > 0)
                <form method="POST" action="{{ route('invoice.export.multiple', $locationId) }}">
                    @csrf

                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">
                                    <x-icon name="receipt-2" />
                                    {{ __('Invoice List') }}
                                </h3>
                                <div class="text-secondary small">
                                    {{ count($invoices) }} {{ __('invoices found') }}
                                </div>
                            </div>

                            <div class="card-actions">
                                <button type="submit" id="exportBtn" class="btn btn-success" disabled>
                                    <x-icon name="download" />
                                    {{ __('Export Selected') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body border-bottom">
                            <label class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="checkAll">
                                <span class="form-check-label">
                                    {{ __('Select all invoices for export') }}
                                </span>
                            </label>
                        </div>

                        <div class="list-group list-group-flush">
                            @foreach ($invoices as $invoice)
                                <div class="list-group-item">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <input type="checkbox" name="ids[]" value="{{ $invoice['id'] }}"
                                                class="row-checkbox form-check-input">
                                        </div>

                                        <div class="col-auto">
                                            <span class="avatar bg-primary-lt text-primary">
                                                <x-icon name="receipt" />
                                            </span>
                                        </div>

                                        <div class="col-12 col-md-3 col-xl-2 text-truncate">
                                            <div class="fw-bold text-primary text-truncate">
                                                {{ $invoice['invoice_no'] ?? '-' }}
                                            </div>
                                            <div class="text-secondary small">
                                                {{ $invoice['invoice_date_text'] }}
                                            </div>
                                        </div>

                                        <div class="col-12 col-md text-truncate">
                                            <div class="fw-semibold text-truncate">
                                                {{ $invoice['room_name_text'] }}
                                            </div>
                                            <div class="text-secondary small text-truncate">
                                                {{ $invoice['building_text'] }}
                                                ·
                                                {{ __('invoice.floor') }}
                                                {{ $invoice['floor_text'] }}
                                                ·
                                                {{ __('invoice.due_date') }}
                                                {{ $invoice['due_date_text'] }}
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-auto text-md-end">
                                            <div class="text-secondary small">
                                                {{ __('invoice.room_rent') }}
                                            </div>
                                            <div class="fw-semibold">
                                                {{ $invoice['money']['room_fee'] }}
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-auto text-md-end">
                                            <div class="text-secondary small">
                                                {{ __('invoice.utility') }}
                                            </div>
                                            <div class="fw-semibold">
                                                {{ $invoice['money']['electric_total'] }}
                                                /
                                                {{ $invoice['money']['water_total'] }}
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-auto text-md-end">
                                            <div class="text-secondary small">
                                                {{ __('invoice.total_amount') }}
                                            </div>
                                            <div class="fw-bold text-danger">
                                                {{ $invoice['money']['grand_total'] }}
                                            </div>
                                        </div>

                                        <div class="col-6 col-md-auto">
                                            <span
                                                class="{{ $invoice['status_meta']['badge'] ?? 'badge bg-secondary-lt' }}">
                                                {{ __($invoice['status_meta']['name'] ?? '-') }}
                                            </span>
                                        </div>

                                        <div class="col-auto ms-md-auto">
                                            <div class="btn-list flex-nowrap">
                                                <button type="button" class="btn btn-icon btn-outline-info"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#{{ $invoice['collapse_id'] }}" aria-expanded="false"
                                                    aria-controls="{{ $invoice['collapse_id'] }}"
                                                    title="{{ __('Details') }}">
                                                    <x-icon name="list-details" />
                                                </button>

                                                <a href="{{ $invoice['urls']['show'] }}"
                                                    class="btn btn-icon btn-outline-primary"
                                                    title="{{ __('invoice.view') ?? 'View' }}">
                                                    <x-icon name="eye" />
                                                </a>

                                                <a href="{{ $invoice['urls']['edit'] }}"
                                                    class="btn btn-icon btn-outline-warning"
                                                    title="{{ __('invoice.edit') ?? 'Edit' }}">
                                                    <x-icon name="edit" />
                                                </a>

                                                <button type="button" class="btn btn-icon btn-outline-danger"
                                                    data-bs-toggle="modal" data-bs-target="#{{ $invoice['id'] }}"
                                                    title="{{ __('invoice.delete_invoice') }}">
                                                    <x-icon name="trash" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="{{ $invoice['collapse_id'] }}" class="collapse mt-3">
                                        <div class="row g-3">
                                            <div class="col-lg-4">
                                                <div class="card card-sm bg-body-tertiary">
                                                    <div class="card-body">
                                                        <h4 class="mb-3">
                                                            <x-icon name="door" />
                                                            {{ __('invoice.room_detail') }}
                                                        </h4>

                                                        <div class="divide-y">
                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('room.building') }}
                                                                </div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['building_text'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('room.floor') }}
                                                                </div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['floor_text'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('room.name') }}
                                                                </div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['room_name_text'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('Created') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['created_at_text'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('Updated') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['updated_at_text'] }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="card card-sm bg-body-tertiary">
                                                    <div class="card-body">
                                                        <h4 class="mb-3">
                                                            <x-icon name="bolt" />
                                                            {{ __('invoice.electric_total') ?? 'Electric' }}
                                                        </h4>

                                                        <div class="divide-y">
                                                            <div class="row py-2">
                                                                <div class="col text-secondary">
                                                                    {{ __('invoice.old_electric') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['calc']['old_electric'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">
                                                                    {{ __('invoice.new_electric') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['calc']['new_electric'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('Used') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['calc']['electric_used'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">
                                                                    {{ __('invoice.electric_rate') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['money']['electric_rate'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('Total') }}</div>
                                                                <div class="col-auto fw-bold text-warning">
                                                                    {{ $invoice['money']['electric_total'] }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-4">
                                                <div class="card card-sm bg-body-tertiary">
                                                    <div class="card-body">
                                                        <h4 class="mb-3">
                                                            <x-icon name="droplet" />
                                                            {{ __('invoice.water_total') ?? 'Water' }}
                                                        </h4>

                                                        <div class="divide-y">
                                                            <div class="row py-2">
                                                                <div class="col text-secondary">
                                                                    {{ __('invoice.old_water') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['calc']['old_water'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">
                                                                    {{ __('invoice.new_water') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['calc']['new_water'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('Used') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['calc']['water_used'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">
                                                                    {{ __('invoice.water_rate') }}</div>
                                                                <div class="col-auto fw-semibold">
                                                                    {{ $invoice['money']['water_rate'] }}</div>
                                                            </div>

                                                            <div class="row py-2">
                                                                <div class="col text-secondary">{{ __('Total') }}</div>
                                                                <div class="col-auto fw-bold text-cyan">
                                                                    {{ $invoice['money']['water_total'] }}</div>
                                                            </div>
                                                        </div>

                                                        <hr>

                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="text-secondary small">
                                                                    {{ __('invoice.total_amount') }}
                                                                </div>
                                                                <div class="h3 text-danger mb-0">
                                                                    {{ $invoice['money']['grand_total'] }}
                                                                </div>
                                                            </div>

                                                            <span
                                                                class="{{ $invoice['status_meta']['badge'] ?? 'badge bg-secondary-lt' }}">
                                                                {{ __($invoice['status_meta']['name'] ?? '-') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card-footer">
                            <div class="row g-3 align-items-center">
                                <div class="col-md">
                                    <div class="text-secondary small">
                                        {{ __('Totals are calculated from the current filtered result.') }}
                                    </div>
                                </div>

                                <div class="col-md-auto">
                                    <div class="btn-list">
                                        <span class="badge bg-success-lt text-success">
                                            {{ __('invoice.room_rent') }}:
                                            {{ number_format($totals['room_fee'], 0, '.', ',') }}៛
                                        </span>
                                        <span class="badge bg-warning-lt text-warning">
                                            {{ __('invoice.electric_total') }}:
                                            {{ number_format($totals['electric_charge'], 0, '.', ',') }}៛
                                        </span>
                                        <span class="badge bg-cyan-lt text-cyan">
                                            {{ __('invoice.water_total') }}:
                                            {{ number_format($totals['water_charge'], 0, '.', ',') }}៛
                                        </span>
                                        <span class="badge bg-danger-lt text-danger">
                                            {{ __('invoice.total_amount') }}:
                                            {{ number_format($totals['grand_total'], 0, '.', ',') }}៛
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <x-empty-state title="{{ __('invoice.no_invoice_found') }}"
                            message="{{ __('invoice.no_invoices_message') }}" svg="svgs/no_result.svg"
                            width="360px" />
                    </div>
                </div>
            @endif

            @foreach ($invoices as $invoice)
                <x-delete-modal id="{{ $invoice['id'] }}" title="{{ __('invoice.delete_invoice') }}"
                    action="{{ route('invoice.destroy', ['id' => $invoice['id'], 'locationId' => $locationId]) }}"
                    item="{{ $invoice['invoice_no'] }}"
                    text="{{ __('invoice.delete_invoice_confirmation_with_id') }}" />
            @endforeach
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const exportBtn = document.getElementById('exportBtn');

            function toggleExportButton() {
                if (!exportBtn) {
                    return;
                }

                exportBtn.disabled = !Array.from(checkboxes).some((checkbox) => checkbox.checked);
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    checkboxes.forEach((checkbox) => {
                        checkbox.checked = checkAll.checked;
                    });

                    toggleExportButton();
                });
            }

            checkboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', function() {
                    if (checkAll) {
                        checkAll.checked = Array.from(checkboxes).every((item) => item.checked);
                    }

                    toggleExportButton();
                });
            });

            toggleExportButton();
        });
    </script>
@endpush
