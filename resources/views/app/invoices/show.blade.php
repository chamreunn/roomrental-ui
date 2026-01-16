@extends('layouts.app')

@section('content')
    {{-- ======= Invoice Header ======= --}}
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <img src="{{ asset('favicon.ico') }}" alt="Logo" width="60">
                </div>
                <div class="col-md-4 text-center">
                    <h2 class="fw-bold mb-0">{{ __('invoice.invoice') }}</h2>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-0"><strong>{{ __('invoice.contact') }}:</strong> 0987654432</p>
                </div>
            </div>

            <hr>

            {{-- ======= Invoice Meta Info ======= --}}
            <div class="row">
                <div class="col-md-6">
                    <p><strong>{{ __('invoice.invoice_no') }}:</strong> {{ $invoice['invoice_no'] }}</p>
                    <p><strong>{{ __('invoice.invoice_date') }}:</strong>
                        {{ \Carbon\Carbon::parse($invoice['invoice_date'])->translatedFormat('d-M-Y') }}
                    </p>
                    <p><strong>{{ __('invoice.due_date') }}:</strong>
                        {{ \Carbon\Carbon::parse($invoice['due_date'])->translatedFormat('d-M-Y') }}
                    </p>
                </div>

                <div class="col-md-6">
                    <p><strong>{{ __('room.name') }}:</strong> {{ $invoice['room']['room_name'] ?? '-' }}</p>
                    <p><strong>{{ __('room.building') }}:</strong> {{ $invoice['room']['building_name'] ?? '-' }}</p>
                    <p><strong>{{ __('room.floor') }}:</strong> {{ $invoice['room']['floor_name'] ?? '-' }}</p>
                    <p><strong>{{ __('room.type') }}:</strong> {{ $invoice['room']['room_type']['type_name'] ?? '-' }}
                    </p>
                </div>
            </div>

            <hr>

            {{-- ======= Client Info ======= --}}
            @if (!empty($invoice['room']['clients'][0]))
                @php $client = $invoice['room']['clients'][0]; @endphp
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>{{ __('client.name') }}:</strong> {{ $client['username'] }}</p>
                        <p><strong>{{ __('client.phone_number') }}:</strong> {{ $client['phone_number'] }}</p>
                        <p><strong>{{ __('client.email') }}:</strong> {{ $client['email'] }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>{{ __('client.gender') }}:</strong> {{ $client['gender'] }}</p>
                        <p><strong>{{ __('client.address') }}:</strong> {{ $client['address'] }}</p>
                        <p><strong>{{ __('client.start_rental_date') }}:</strong>
                            {{ \Carbon\Carbon::parse($client['start_rental_date'])->translatedFormat('d-M-Y') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ======= Invoice Details ======= --}}
    <div class="card">
        <div class="card-body">
            <h4 class="fw-bold mb-3 d-print-none">{{ __('invoice.details') }}</h4>
            <div class="row align-items-center">
                <div class="col-md-4">
                    <img src="{{ asset('favicon.ico') }}" alt="Logo" width="60">
                </div>
                <div class="col-md-4 text-center">
                    <h2 class="fw-bold mb-0">{{ __('invoice.invoice') }}</h2>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-0"><strong>{{ __('invoice.contact') }}:</strong> 0987654432</p>
                </div>
            </div>
            <div class="d-flex gap-2 mb-3">
                <span><strong>{{ __('room.name') }}:</strong> {{ $invoice['room']['room_name'] }}</span>
                <span><strong>{{ __('invoice.month') }}:</strong>
                    {{ Carbon\Carbon::parse($invoice['invoice_date'])->translatedFormat('F-Y') }}</span>
            </div>
            <table class="table table-bordered border-dark text-dark table-vcenter">
                <tbody>
                    <tr>
                        <th>{{ __('invoice.old_electric') }}</th>
                        <td>{{ $invoice['old_electric'] }}</td>
                        <th>{{ __('invoice.new_electric') }}</th>
                        <td>{{ $invoice['new_electric'] }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('invoice.electric_rate') }}</th>
                        <td>{{ $invoice['electric_rate'] }}</td>
                        <th>{{ __('invoice.electric_total') }}</th>
                        <td>{{ number_format($invoice['electric_total'], 2) }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('invoice.old_water') }}</th>
                        <td>{{ $invoice['old_water'] }}</td>
                        <th>{{ __('invoice.new_water') }}</th>
                        <td>{{ $invoice['new_water'] }}</td>
                    </tr>
                    <tr>
                        <th>{{ __('invoice.water_rate') }}</th>
                        <td>{{ $invoice['water_rate'] }}</td>
                        <th>{{ __('invoice.water_total') }}</th>
                        <td>{{ number_format($invoice['water_total'], 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">{{ __('invoice.room_rent') }}</th>
                        <td>{{ number_format($invoice['room_fee'], 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end">{{ __('invoice.other_charge') }}</th>
                        <td>{{ number_format($invoice['other_charge'], 2) }}</td>
                    </tr>
                    <tr class="fw-bold table-dark">
                        <th colspan="3" class="text-end">{{ __('invoice.total_amount') }}</th>
                        <td>{{ number_format($invoice['grand_total'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <strong class="text-danger">
                    <span class="fs-4 me-1">***</span>
                    អតិថិជនត្រូវបង់ប្រាក់បន្ទប់ អគ្គិសនី និងទឹកអោយបានទាន់ពេលវេលា។ សូមអរគុណ។
                </strong>
            </div>

            {{-- ======= Actions ======= --}}
            <div class="d-flex justify-content-end gap-3 mt-4 d-print-none">
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                    <x-icon name="printer" /> {{ __('invoice.print') }}
                </button>
                <a href="{{ route('invoice.user_index', $locationId) }}" class="btn btn-secondary">
                    {{ __('invoice.back') }}
                </a>
            </div>
        </div>
    </div>
@endsection
