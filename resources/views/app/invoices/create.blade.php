@extends('layouts.app')

@section('content')

    <div class="row g-3">

        {{-- ===== Room Details ===== --}}
        <div class="col-12 d-print-none">
            <div class="card">
                <div class="card-body">
                    <h4 class="fw-bold">{{ __('invoice.room_detail') }}</h4>
                    <div class="row g-3">
                        <div class="col-md-4"><strong>{{ __('room.name') }}:</strong> {{ $room['room_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.building') }}:</strong> {{ $room['building_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.floor') }}:</strong> {{ $room['floor_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.location') }}:</strong>
                            {{ $room['location']['location_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.type') }}:</strong> {{ $room['room_type']['type_name'] }}
                        </div>
                        <div class="col-md-4"><strong>{{ __('room.size') }}:</strong>
                            {{ $room['room_type']['room_size'] }}
                        </div>
                        <div class="col-md-4"><strong>{{ __('roomtype.price') }}:</strong>
                            ${{ number_format($room['room_type']['price'], 2) }}</div>
                        <div class="col-md-12 mt-2"><strong>{{ __('room.description') }}:</strong>
                            <p class="text-muted mb-0">{{ $room['description'] ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Clients Renting This Room ===== --}}
        @if ($clients->isNotEmpty())
            <div class="col-12 d-print-none">
                <div class="card">
                    <div class="card-body">
                        <h4 class="fw-bold">{{ __('room.clients') }}</h4>
                        <div class="row row-cards g-2">
                            @foreach ($clients as $client)
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body d-flex align-items-center">
                                            <span
                                                class="avatar me-3 bg-{{ $client['gender'] == 'ប្រុស' ? 'blue' : 'pink' }} text-white">
                                                {{ strtoupper(substr($client['username'], 0, 1)) }}
                                            </span>
                                            <div class="flex-fill">
                                                <div class="fw-bold text-primary mb-1">
                                                    {{ $client['username'] }}
                                                    <span class="mx-2 {{ __($client['clientstatus']['badge']) }}">
                                                        {{ __($client['clientstatus']['name']) }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small">
                                                    <strong>{{ __('client.phone') }}:</strong>
                                                    {{ $client['phone_number'] }}
                                                </div>
                                                <div class="text-muted small">
                                                    <strong>{{ __('client.start_rental_date') }}:</strong>
                                                    {{ $client['start_rental_date'] }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== Invoice Preview ===== --}}
        @if (!empty($preview))
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-dark">
                        <div class="row g-3">
                            <div class="col-4">
                                <img src="{{ asset('favicon.ico') }}" width="60" alt="">
                            </div>
                            <div class="col-4 d-flex justify-content-center align-items-center">
                                <div class="text-center fw-bold fs-1">{{ __('invoice.invoice') }}</div>
                            </div>
                            <div class="col-4 text-end">
                                <p><strong>{{ __('invoice.contact') }} : </strong>0987654432</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <span><strong>{{ __('room.name') }}:</strong> {{ $room['room_name'] }}</span>
                            <span><strong>{{ __('invoice.month') }}:</strong>
                                {{ Carbon\Carbon::parse($preview['month'])->translatedFormat('F-Y') }}</span>
                        </div>
                        {{-- ===== Invoice Details ===== --}}
                        <table class="table table-bordered mt-3 border border-dark">
                            <tbody class="border border-dark">
                                <tr>
                                    <th>{{ __('invoice.old_electric') }}</th>
                                    <td>{{ $preview['old_electric'] }}</td>
                                    <th>{{ __('invoice.new_electric') }}</th>
                                    <td>{{ $preview['new_electric'] }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('invoice.electric_rate') }}</th>
                                    <td>{{ $preview['electric_rate'] }}</td>
                                    <th>{{ __('invoice.electric_total') }}</th>
                                    <td>{{ number_format($preview['electric_total'], 2) }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('invoice.old_water') }}</th>
                                    <td>{{ $preview['old_water'] }}</td>
                                    <th>{{ __('invoice.new_water') }}</th>
                                    <td>{{ $preview['new_water'] }}</td>
                                </tr>
                                <tr>
                                    <th>{{ __('invoice.water_rate') }}</th>
                                    <td>{{ $preview['water_rate'] }}</td>
                                    <th>{{ __('invoice.water_total') }}</th>
                                    <td>{{ number_format($preview['water_total'], 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">{{ __('invoice.room_rent') }}</th>
                                    <td>{{ $preview['room_rent'] }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">{{ __('invoice.other_charge') }}</th>
                                    <td>{{ $preview['other_charge'] }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <th colspan="3" class="text-end">{{ __('invoice.total_amount') }}</th>
                                    <td>{{ number_format($preview['grand_total'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <strong class="text-danger"><span class="fs-3 me-1">***</span>អតិថិជនត្រូវបង់ថ្ងៃបន្ទប់ អគ្គិសនី
                            និងទឹកអោយបានទាន់ពេលវេលា។ សូមអរគុណ។</strong>

                        {{-- ===== Print and Save ===== --}}
                        <div class="d-flex gap-3 align-items-center justify-content-end mt-3 d-print-none">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <x-icon name="printer" /> {{ __('invoice.print') }}
                            </button>
                            <form method="POST" action="{{ route('invoice.store') }}">
                                @csrf
                                @foreach ($preview as $key => $value)
                                    <input type="text" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <input type="hidden" name="room_id" value="{{ $room['id'] }}">
                                <button type="submit" class="btn btn-success">
                                    {{ __('invoice.save_invoice') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== Generate Invoice Form ===== --}}
        <div class="col-12 d-print-none">
            <div class="card">
                <form method="POST" action="{{ route('invoices.preview', [$room['id'], $room['location']['id']]) }}">
                    @csrf
                    <div class="card-body">
                        <h4 class="fw-bold mb-3">{{ __('invoice.generate_invoice') }}</h4>
                        <input type="hidden" name="room_id" value="{{ $room['id'] }}">

                        <div class="row g-3">
                            @php
                                $old = old();
                                $currentMonth = \Carbon\Carbon::now()->format('Y-m-d'); // e.g. "October 2025"
                            @endphp

                            @foreach (['month', 'old_electric', 'new_electric', 'electric_rate', 'old_water', 'new_water', 'water_rate', 'other_charge'] as $field)
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                        {{ __('invoice.' . $field) }}
                                    </label>

                                    {{-- ✅ Apply "monthpicker" only to the month field --}}
                                    @if ($field === 'month')
                                        <input type="text" name="month" class="form-control datepicker"
                                            value="{{ $old['month'] ?? ($preview['month'] ?? $currentMonth) }}"
                                            placeholder="{{ __('invoice.select_month') }}" autocomplete="off">
                                    @else
                                        <input type="number" step="any" name="{{ $field }}"
                                            class="form-control" value="{{ $old[$field] ?? ($preview[$field] ?? '') }}"
                                            placeholder="{{ __('invoice.' . $field) }}" autocomplete="off">
                                    @endif

                                    @error($field)
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="{{ route('invoice.create', [$room['id'], $room['location']['id']]) }}"
                            class="btn btn-warning btn-animate-icon btn-animate-icon-rotate">
                            {{ __('invoice.clear') }}
                            <x-icon name="refresh" class="icon-end" />
                        </a>
                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary">
                                {{ __('invoice.invoice_preview') }}
                            </button>
                            <a href="{{ route('invoice.index') }}" class="btn btn-secondary">
                                {{ __('invoice.cancel') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
@endsection
