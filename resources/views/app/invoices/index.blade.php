@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            {{-- ===== Filter & Search ===== --}}
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="status" class="form-label">{{ __('invoice.status') ?? 'Status' }}:</label>
                    <select name="status" id="status" class="form-select tom-select">
                        <option value="">{{ __('invoice.all') ?? 'All' }}</option>
                        @foreach (\App\Enum\InvoiceStatus::all() as $key => $label)
                            @php
                                $status = \App\Enum\InvoiceStatus::getStatus($key);
                            @endphp
                            <option value="{{ $key }}"
                                {{ (string) $filter_status === (string) $key ? 'selected' : '' }}
                                data-custom-properties="<span class='{{ $status['badge'] }} badge mx-0'>{{ __($status['name']) }}</span>">
                                {{ __($status['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label for="search" class="form-label">{{ __('invoice.search') ?? 'Search' }}:</label>
                    <input type="text" name="search" id="search" class="form-control"
                        placeholder="{{ __('invoice.search_placeholder') ?? 'Invoice No / Room' }}"
                        value="{{ $search }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary mt-4">
                        {{ __('invoice.search') ?? 'Filter' }}
                        <x-icon name="search" class="icon-end" />
                    </button>
                    <a href="{{ route('invoice.index') }}"
                        class="btn btn-secondary mt-4">{{ __('invoice.reset') ?? 'Reset' }}</a>
                </div>
            </form>
        </div>

        {{-- ===== Invoice Table ===== --}}
        @if (!empty($invoices) && count($invoices) > 0)
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-vcenter text-center">
                    <thead class="table-dark">
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
                            @php
                                $status = \App\Enum\InvoiceStatus::getStatus($invoice['status']);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $invoice['invoice_no'] }}</td>
                                <td>{{ $invoice['room']['room_name'] ?? '-' }}</td>
                                <td>{{ $invoice['invoice_date'] }}</td>
                                <td>{{ $invoice['due_date'] }}</td>
                                <td>{{ number_format($invoice['room_fee'], 0, '.', ',') }}</td>
                                <td>{{ number_format($invoice['electric_rate'] * ($invoice['new_electric'] - $invoice['old_electric']), 0, '.', ',') }}</td>
                                <td>{{ number_format($invoice['water_rate'] * ($invoice['new_water'] - $invoice['old_water']), 0, '.', ',') }}</td>
                                <td>{{ number_format($invoice['other_charge'], 0, '.', ',') }}</td>
                                <td>{{ number_format($invoice['total'], 0, '.', ',') }}</td>
                                <td>
                                    <span class="{{ $status['badge'] }}">
                                        {{ __($status['name']) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('invoice.show', $invoice['id']) }}"
                                            class="btn btn-sm btn-primary"
                                            target="_blank">{{ __('invoice.view') ?? 'View' }}</a>
                                        <a href="{{ route('invoice.edit', $invoice['id']) }}"
                                            class="btn btn-sm btn-warning">{{ __('invoice.edit') ?? 'Edit' }}</a>
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
            <x-empty-state 
                title="{{ __('invoice.no_invoice_found') }}" 
                message="{{ __('invoice.no_invoices_message') }}"
                svg="svgs/no_result.svg" 
                width="450px" 
            />
        @endif
    </div>
@endsection
