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
                            <option value="{{ $key }}" {{ (string) $filter_status === (string) $key ? 'selected' : '' }}>
                                {{ __($status['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Location --}}
                <div class="col-lg-2 col-md-4">
                    <select name="location" id="location" class="form-select tom-select">
                        <option value="">{{ __('invoice.location') ?? 'All' }}</option>
                        @foreach ($locations as $loc)
                            <option value="{{ $loc['id'] }}" {{ $filter_location == $loc['id'] ? 'selected' : '' }}>
                                {{ $loc['location_name'] }}
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
                        value="{{ $filter_building }}" placeholder="{{__('invoice.building')}}">
                </div>

                {{-- Floor Name --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="floor_name" id="floor_name" class="form-control" value="{{ $filter_floor }}"
                        placeholder="{{__('invoice.floor')}}">
                </div>

                {{-- Room Name --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="room_name" id="room_name" class="form-control" value="{{ $filter_room }}"
                        placeholder="{{__('invoice.room')}}">
                </div>

                {{-- Month --}}
                <div class="col-lg-2 col-md-4">
                    <input type="month" name="month" id="month" class="form-control monthpicker" value="{{ $filter_month }}"
                        placeholder="{{ __('invoice.month_placeholder') }}" autocomplete="off">
                </div>

                {{-- From Date --}}
                <div class="col-lg-2 col-md-4">
                    <input type="date" name="from_date" id="from_date" class="form-control datepicker"
                        value="{{ $from_date }}" placeholder="{{ __('invoice.from_date') }}" autocomplete="off">
                </div>

                {{-- To Date --}}
                <div class="col-lg-2 col-md-4">
                    <input type="date" name="to_date" id="to_date" class="form-control datepicker" value="{{ $to_date }}"
                        placeholder="{{ __('invoice.to_date') }}" autocomplete="off">
                </div>

                {{-- Search --}}
                <div class="col-lg-2 col-md-4">
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="{{ __('invoice.search_placeholder') ?? 'Invoice No / Room' }}" value="{{ $search }}">
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
                            <a href="{{ route('invoice.index') }}" class="btn btn-secondary w-100">
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
                                            @php
                                                $status = \App\Enum\InvoiceStatus::getStatus($invoice['status']);
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $invoice['invoice_no'] }}</td>
                                                <td>{{ $invoice['room']['room_name'] ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($invoice['invoice_date'])->translatedFormat('d-M-Y') }}</td>
                                                <td>{{ number_format($invoice['room_fee'], 0, '.', ',') }}</td>
                                                <td>{{ number_format($invoice['electric_rate'] * ($invoice['new_electric'] - $invoice['old_electric']), 0, '.', ',') }}
                                                </td>
                                                <td>
                                                    {{ number_format(
                                    $invoice['water_rate'] * ($invoice['new_water'] - $invoice['old_water']),
                                    0,
                                    '.',
                                    ','
                                ) }}
                                                </td>
                                                <td>{{ number_format($invoice['other_charge'], 0, '.', ',') }}</td>
                                                <td><span class="text-danger">{{ number_format($invoice['total'], 0, '.', ',') }}</span></td>
                                                <td>
                                                    <form action="{{ route('invoice.updateStatus', [$invoice['id']]) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="dropdown d-inline-block mb-0">
                                                            <button class="btn {{ $status['badge'] }} btn-sm dropdown-toggle" type="button"
                                                                data-bs-toggle="dropdown">
                                                                <x-icon name="pencil" class="me-1" /> {{ __($status['name']) }}
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
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a href="{{ route('invoice.show', $invoice['id']) }}" class="btn btn-sm btn-primary"
                                                            data-bs-toggle="tooltip" title="{{ __('invoice.view') ?? 'View' }}">
                                                            {{-- {{ __('invoice.view') ?? 'View' }} --}}
                                                            <x-icon name="eye" class="me-0" />
                                                        </a>
                                                        <a href="{{ route('invoice.edit', $invoice['id']) }}" data-bs-toggle="tooltip"
                                                            title="{{ __('invoice.edit') ?? 'Edit' }}" class="btn btn-sm btn-warning">
                                                            {{-- {{ __('invoice.edit') ?? 'Edit' }} --}}
                                                            <x-icon name="edit" class="me-0" />
                                                        </a>
                                                        <a href="#" data-bs-toggle="modal" data-bs-target="#{{ $invoice['id'] }}"
                                                            class="btn btn-sm btn-danger">
                                                            {{-- {{ __('invoice.edit') ?? 'Edit' }} --}}
                                                            <x-icon name="trash" class="me-0" />
                                                        </a>
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
                <x-empty-state title="{{ __('invoice.no_invoice_found') }}" message="{{ __('invoice.no_invoices_message') }}"
                    svg="svgs/no_result.svg" width="450px" />
            @endif
        </div>
    </div>

    @foreach ($invoices as $invoice)

        <x-delete-modal id="{{ $invoice['id'] }}" title="{{ __('invoice.delete_invoice') }}"
            action="{{ route('invoice.destroy', $invoice['id']) }}" item="{{ $invoice['invoice_no'] }}"
            text="{{ __('invoice.delete_invoice_confirmation_with_id') }}" />

    @endforeach

@endsection
