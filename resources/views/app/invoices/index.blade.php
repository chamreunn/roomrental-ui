@extends('layouts.app')

@section('content')

    <div class="card">
        <div class="card-body">
            {{-- ===== Filter & Search ===== --}}
            <form method="GET" class="row g-3 align-items-end">

                {{-- Status --}}
                <div class="col-lg-2 col-md-4">
                    <select name="status" id="status" class="form-select tom-select">
                        <option value="">{{ __('invoice.status') ?? 'All' }}</option>
                        @foreach (\App\Enum\InvoiceStatus::all() as $key => $label)
                            @php $status = \App\Enum\InvoiceStatus::getStatus($key); @endphp
                            <option value="{{ $key }}"
                                {{ (string) $filter_status === (string) $key ? 'selected' : '' }}>
                                {{ __($status['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Room Type --}}
                <div class="col-lg-2 col-md-4">
                    <select name="room_type" id="room_type" class="form-select tom-select">
                        <option value="">{{ __('invoice.room_type') ?? 'All' }}</option>
                        @foreach ($roomTypes as $type)
                            <option value="{{ $type['id'] }}" {{ $filter_room_type == $type['id'] ? 'selected' : '' }}>
                                {{ $type['type_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Building Name --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="building_name" id="building_name" class="form-control"
                        value="{{ $filter_building }}" placeholder="{{ __('invoice.building') }}">
                </div>

                {{-- Floor Name --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="floor_name" id="floor_name" class="form-control"
                        value="{{ $filter_floor }}" placeholder="{{ __('invoice.floor') }}">
                </div>

                {{-- Room Name --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="room_name" id="room_name" class="form-control" value="{{ $filter_room }}"
                        placeholder="{{ __('invoice.room') }}">
                </div>

                {{-- Month --}}
                <div class="col-lg-2 col-md-4">
                    <input type="month" name="month" id="month" class="form-control monthpicker"
                        value="{{ $filter_month }}" placeholder="{{ __('invoice.month_placeholder') }}"
                        autocomplete="off">
                </div>

                {{-- From Date --}}
                <div class="col-lg-2 col-md-4">
                    <input type="date" name="from_date" id="from_date" class="form-control datepicker"
                        value="{{ $from_date }}" placeholder="{{ __('invoice.from_date') }}" autocomplete="off">
                </div>

                {{-- To Date --}}
                <div class="col-lg-2 col-md-4">
                    <input type="date" name="to_date" id="to_date" class="form-control datepicker"
                        value="{{ $to_date }}" placeholder="{{ __('invoice.to_date') }}" autocomplete="off">
                </div>

                {{-- Search --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="{{ __('invoice.search_placeholder') ?? 'Invoice No / Room' }}"
                        value="{{ $search }}">
                </div>

                {{-- Buttons --}}
                <div class="ol-lg-2 col-md-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ __('invoice.filter') ?? 'Filter' }}
                                <x-icon name="search" class="icon-end" />
                            </button>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('invoice.user_index', $locationId) }}" class="btn btn-secondary w-100">
                                {{ __('invoice.reset') ?? 'Reset' }}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-body">
            {{-- ===== Invoice Table ===== --}}
            @if (!empty($invoices) && count($invoices) > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-vcenter text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('invoice.invoice_no') ?? 'Invoice No' }}</th>
                                <th>{{ __('invoice.room_detail') ?? 'Room' }}</th>
                                <th>{{ __('invoice.month') ?? 'Invoice Date' }}</th>
                                <th>{{ __('invoice.due_date') ?? 'Due Date' }}</th>
                                <th>{{ __('invoice.room_rent') ?? 'Room Fee' }}</th>
                                <th>{{ __('invoice.electric_total') ?? 'Electric' }}</th>
                                <th>{{ __('invoice.water_total') ?? 'Water' }}</th>
                                <th>{{ __('invoice.other_charge') ?? 'Other' }}</th>
                                <th>{{ __('invoice.total_amount') ?? 'Total' }}</th>
                                <th>{{ __('invoice.status') ?? 'Status' }}</th>
                                <th>{{ __('invoice.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoices as $index => $invoice)
                                @php($collapseId = 'inv-details-' . ($invoice['id'] ?? $index))

                                <tr>
                                    <td>{{ $index + 1 }}</td>

                                    <td class="fw-bold">{{ $invoice['invoice_no'] ?? '-' }}</td>

                                    <td>
                                        <div class="fw-semibold">{{ $invoice['room']['room_name'] ?? '-' }}</div>
                                        <div class="text-muted small">
                                            {{ $invoice['room']['building_name'] ?? '-' }} •
                                            {{ $invoice['room']['floor_name'] ?? '-' }}
                                        </div>
                                    </td>

                                    <td>{{ \Carbon\Carbon::parse($invoice['invoice_date'])->translatedFormat('d-M-Y') }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($invoice['due_date'])->translatedFormat('d-M-Y') }}</td>

                                    <td>{{ number_format($invoice['calc']['room_fee'] ?? 0, 0, '.', ',') }}</td>
                                    <td>{{ number_format($invoice['calc']['electric_total'] ?? 0, 0, '.', ',') }}</td>
                                    <td>{{ number_format($invoice['calc']['water_total'] ?? 0, 0, '.', ',') }}</td>
                                    <td>{{ number_format($invoice['calc']['other_charge'] ?? 0, 0, '.', ',') }}</td>

                                    <td>
                                        <span class="text-danger fw-bold">
                                            {{ number_format($invoice['calc']['grand_total'] ?? ($invoice['total'] ?? 0), 0, '.', ',') }}
                                        </span>
                                    </td>

                                    {{-- Status dropdown (same as yours) --}}
                                    <td>
                                        @php($st = \App\Enum\InvoiceStatus::getStatus($invoice['status']))
                                        <form
                                            action="{{ route('invoice.updateStatus', ['id' => $invoice['id'], 'locationId' => $locationId]) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <div class="dropdown d-inline-block mb-2">
                                                <button class="btn {{ $st['badge'] }} btn-sm dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown">
                                                    <x-icon name="pencil" class="me-1" /> {{ __($st['name']) }}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @foreach ($statuses as $key => $status)
                                                        <li>
                                                            <button type="submit" name="status"
                                                                value="{{ $key }}" class="dropdown-item">
                                                                <span
                                                                    class="{{ $status['text'] }}">{{ __($status['name']) }}</span>
                                                            </button>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </form>
                                    </td>

                                    <td>
                                        <div class="d-flex justify-content-center gap-1 flex-wrap">
                                            {{-- Details toggle --}}
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                                aria-expanded="false" aria-controls="{{ $collapseId }}"
                                                title="Details">
                                                <x-icon name="list-details" class="me-0" />
                                            </button>

                                            <a href="{{ route('invoice.show', ['id' => $invoice['id'], 'locationId' => $locationId]) }}"
                                                class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                title="{{ __('invoice.view') ?? 'View' }}">
                                                <x-icon name="eye" class="me-0" />
                                            </a>

                                            <a href="{{ route('invoice.edit', ['id' => $invoice['id'], 'locationId' => $locationId]) }}"
                                                data-bs-toggle="tooltip" title="{{ __('invoice.edit') ?? 'Edit' }}"
                                                class="btn btn-sm btn-warning">
                                                <x-icon name="edit" class="me-0" />
                                            </a>

                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#{{ $invoice['id'] }}" class="btn btn-sm btn-danger">
                                                <x-icon name="trash" class="me-0" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Details row --}}
                                <tr class="bg-light">
                                    <td colspan="12" class="p-0 border-top-0">
                                        <div id="{{ $collapseId }}" class="collapse">
                                            <div class="p-3">
                                                <div class="row g-3">

                                                    {{-- Room / Meta --}}
                                                    <div class="col-lg-4">
                                                        <div class="fw-bold mb-2">{{ __('invoice.room_detail') }}</div>
                                                        <div class="small">
                                                            <div><strong>{{ __('room.building') }}:</strong>
                                                                {{ $invoice['room']['building_name'] ?? '-' }}</div>
                                                            <div><strong>{{ __('room.floor') }}:</strong>
                                                                {{ $invoice['room']['floor_name'] ?? '-' }}</div>
                                                            <div><strong>{{ __('room.name') }}:</strong>
                                                                {{ $invoice['room']['room_name'] ?? '-' }}</div>

                                                            <div class="mt-2">
                                                                <strong>Created:</strong>
                                                                {{ isset($invoice['created_at']) ? \Carbon\Carbon::parse($invoice['created_at'])->translatedFormat('d-M-Y H:i') : '-' }}
                                                            </div>
                                                            <div>
                                                                <strong>Updated:</strong>
                                                                {{ isset($invoice['updated_at']) ? \Carbon\Carbon::parse($invoice['updated_at'])->translatedFormat('d-M-Y H:i') : '-' }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Electric --}}
                                                    <div class="col-lg-4">
                                                        <div class="fw-bold mb-2">
                                                            {{ __('invoice.electric_total') ?? 'Electric' }}</div>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered mb-0">
                                                                <tr>
                                                                    <th>{{ __('invoice.old_electric') }}</th>
                                                                    <td>{{ $invoice['calc']['old_electric'] ?? 0 }}</td>
                                                                    <th>{{ __('invoice.new_electric') }}</th>
                                                                    <td>{{ $invoice['calc']['new_electric'] ?? 0 }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Used</th>
                                                                    <td>{{ $invoice['calc']['electric_used'] ?? 0 }}</td>
                                                                    <th>{{ __('invoice.electric_rate') }}</th>
                                                                    <td>{{ number_format($invoice['calc']['electric_rate'] ?? 0, 2) }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="table-light">
                                                                    <th colspan="3" class="text-end">Total</th>
                                                                    <td class="fw-bold">
                                                                        {{ number_format($invoice['calc']['electric_total'] ?? 0, 2) }}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                    </div>

                                                    {{-- Water + Summary --}}
                                                    <div class="col-lg-4">
                                                        <div class="fw-bold mb-2">
                                                            {{ __('invoice.water_total') ?? 'Water' }}</div>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered mb-0">
                                                                <tr>
                                                                    <th>{{ __('invoice.old_water') }}</th>
                                                                    <td>{{ $invoice['calc']['old_water'] ?? 0 }}</td>
                                                                    <th>{{ __('invoice.new_water') }}</th>
                                                                    <td>{{ $invoice['calc']['new_water'] ?? 0 }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Used</th>
                                                                    <td>{{ $invoice['calc']['water_used'] ?? 0 }}</td>
                                                                    <th>{{ __('invoice.water_rate') }}</th>
                                                                    <td>{{ number_format($invoice['calc']['water_rate'] ?? 0, 2) }}
                                                                    </td>
                                                                </tr>
                                                                <tr class="table-light">
                                                                    <th colspan="3" class="text-end">Total</th>
                                                                    <td class="fw-bold">
                                                                        {{ number_format($invoice['calc']['water_total'] ?? 0, 2) }}
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>

                                                        <div class="mt-3 fw-bold">
                                                            {{ __('invoice.total_amount') ?? 'Summary' }}</div>
                                                        <div class="small">
                                                            <div><strong>{{ __('invoice.room_rent') }}:</strong>
                                                                {{ number_format($invoice['calc']['room_fee'] ?? 0, 2) }}
                                                            </div>
                                                            <div><strong>{{ __('invoice.other_charge') }}:</strong>
                                                                {{ number_format($invoice['calc']['other_charge'] ?? 0, 2) }}
                                                            </div>
                                                            <div class="text-danger">
                                                                <strong>{{ __('invoice.total_amount') }}:</strong>
                                                                {{ number_format($invoice['calc']['grand_total'] ?? 0, 2) }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">{{ __('invoice.totals') ?? 'Totals' }}</th>
                                <th>{{ number_format($totals['room_fee'], 0, '.', ',') }}</th>
                                <th>{{ number_format($totals['electric_charge'], 0, '.', ',') }}</th>
                                <th>{{ number_format($totals['water_charge'], 0, '.', ',') }}</th>
                                <th>-</th>
                                <th>-</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <x-empty-state title="{{ __('invoice.no_invoice_found') }}"
                    message="{{ __('invoice.no_invoices_message') }}" svg="svgs/no_result.svg" width="450px" />
            @endif
        </div>
    </div>

    @foreach ($invoices as $invoice)
        <x-delete-modal id="{{ $invoice['id'] }}" title="{{ __('invoice.delete_invoice') }}"
            action="{{ route('invoice.destroy', ['id' => $invoice['id'], 'locationId' => $locationId]) }}"
            item="{{ $invoice['invoice_no'] }}" text="{{ __('invoice.delete_invoice_confirmation_with_id') }}" />
    @endforeach

@endsection
