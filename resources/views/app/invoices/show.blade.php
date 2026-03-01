@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title">{{ __('invoice.invoice') }} — {{ $invoice['invoice_no'] }}</h3>
            <div class="d-flex gap-2">
                <a href="{{ route('invoice.export', [$invoice['id'], $locationId]) }}" class="btn btn-outline-success btn-sm">
                    <x-icon name="download" /> {{ __('Export Invoice') }}
                </a>
                <a href="{{ route('invoice.user_index', $locationId) }}" class="btn btn-secondary btn-sm">
                    <x-icon name="arrow-left" /> {{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="card-body">

            {{-- ======= Meta Info ======= --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted w-50 fw-semibold">{{ __('invoice.invoice_no') }}</th>
                            <td>{{ $invoice['invoice_no'] }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('invoice.invoice_date') }}</th>
                            <td>{{ \Carbon\Carbon::parse($invoice['invoice_date'])->translatedFormat('d-M-Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('invoice.due_date') }}</th>
                            <td>{{ \Carbon\Carbon::parse($invoice['due_date'])->translatedFormat('d-M-Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('invoice.month') }}</th>
                            <td>{{ \Carbon\Carbon::parse($invoice['invoice_date'])->translatedFormat('F Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-muted w-50 fw-semibold">{{ __('room.name') }}</th>
                            <td>{{ $invoice['room']['room_name'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('room.building') }}</th>
                            <td>{{ $invoice['room']['building_name'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('room.floor') }}</th>
                            <td>{{ $invoice['room']['floor_name'] ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-semibold">{{ __('room.type') }}</th>
                            <td>{{ $invoice['room']['room_type']['type_name'] ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <hr>

            {{-- ======= Client Info ======= --}}
            @if (!empty($invoice['room']['clients'][0]))
                @php $client = $invoice['room']['clients'][0]; @endphp
                <h5 class="mb-3 fw-bold">{{ __('client.information') }}</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted w-50 fw-semibold">{{ __('client.name') }}</th>
                                <td>{{ $client['username'] }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">{{ __('client.phone_number') }}</th>
                                <td>{{ $client['phone_number'] }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">{{ __('client.email') }}</th>
                                <td>{{ $client['email'] ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted w-50 fw-semibold">{{ __('client.gender') }}</th>
                                <td>{{ $client['gender'] === 'm' ? __('tenant.male') : __('tenant.female') }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">{{ __('client.address') }}</th>
                                <td>{{ $client['address'] }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-semibold">{{ __('client.start_rental_date') }}</th>
                                <td>{{ \Carbon\Carbon::parse($client['start_rental_date'])->translatedFormat('d-M-Y') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
            @endif

            {{-- ======= Invoice Details ======= --}}
            <h5 class="mb-3 fw-bold">{{ __('invoice.details') }}</h5>
            <table class="table table-bordered border-dark text-dark table-vcenter">
                <tbody>
                    <tr>
                        <th class="fw-semibold">{{ __('invoice.old_electric') }}</th>
                        <td>{{ $invoice['old_electric'] }}</td>
                        <th class="fw-semibold">{{ __('invoice.new_electric') }}</th>
                        <td>{{ $invoice['new_electric'] }}</td>
                    </tr>
                    <tr>
                        <th class="fw-semibold">{{ __('invoice.electric_rate') }}</th>
                        <td>{{ number_format($invoice['electric_rate'], 2) }} ៛</td>
                        <th class="fw-semibold">{{ __('invoice.electric_total') }}</th>
                        <td>{{ number_format($invoice['electric_total'], 2) }} ៛</td>
                    </tr>
                    <tr>
                        <th class="fw-semibold">{{ __('invoice.old_water') }}</th>
                        <td>{{ $invoice['old_water'] }}</td>
                        <th class="fw-semibold">{{ __('invoice.new_water') }}</th>
                        <td>{{ $invoice['new_water'] }}</td>
                    </tr>
                    <tr>
                        <th class="fw-semibold">{{ __('invoice.water_rate') }}</th>
                        <td>{{ number_format($invoice['water_rate'], 2) }} ៛</td>
                        <th class="fw-semibold">{{ __('invoice.water_total') }}</th>
                        <td>{{ number_format($invoice['water_total'], 2) }} ៛</td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end fw-semibold">{{ __('invoice.room_rent') }}</th>
                        <td>{{ number_format($invoice['room_fee'], 2) }} ៛</td>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-end fw-semibold">{{ __('invoice.other_charge') }}</th>
                        <td>{{ number_format($invoice['other_charge'], 2) }} ៛</td>
                    </tr>
                    <tr class="table-dark">
                        <th colspan="3" class="text-end fw-bold">{{ __('invoice.total_amount') }}</th>
                        <td class="fw-bold">{{ number_format($invoice['grand_total'], 2) }} ៛</td>
                    </tr>
                </tbody>
            </table>

            {{-- ======= Notice ======= --}}
            <div class="alert alert-danger mt-3 py-2">
                <strong>*** អតិថិជនត្រូវបង់ប្រាក់បន្ទប់ អគ្គិសនី និងទឹកអោយបានទាន់ពេលវេលា។ សូមអរគុណ។</strong>
            </div>

        </div>
    </div>
@endsection
