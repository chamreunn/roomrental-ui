@extends('layouts.app')

@section('content')
    <div class="row g-3">

        {{-- ===== Room Details ===== --}}
        <div class="col-12 d-print-none">
            <div class="card">
                <div class="card-body">
                    <h4 class="fw-bold">{{ __('room.details') }}</h4>
                    <div class="row g-3">
                        <div class="col-md-4"><strong>{{ __('room.name') }}:</strong> {{ $room['room_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.building') }}:</strong> {{ $room['building_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.floor') }}:</strong> {{ $room['floor_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.location') }}:</strong>
                            {{ $room['location']['location_name'] }}</div>
                        <div class="col-md-4"><strong>{{ __('room.type') }}:</strong> {{ $room['room_type']['type_name'] }}
                        </div>
                        <div class="col-md-4"><strong>{{ __('room.size') }}:</strong> {{ $room['room_type']['room_size'] }}
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
        @if($clients->isNotEmpty())
            <div class="col-12 d-print-none">
                <div class="card">
                    <div class="card-body">
                        <h4 class="fw-bold">{{ __('room.clients') }}</h4>
                        <div class="row row-cards g-2">
                            @foreach($clients as $client)
                                <div class="col-md-4">
                                    <div class="card card-sm">
                                        <div class="card-body d-flex align-items-center">
                                            <span
                                                class="avatar me-3 bg-{{ $client['gender'] == 'ប្រុស' ? 'blue' : 'pink' }} text-white">
                                                {{ strtoupper(substr($client['username'], 0, 1)) }}
                                            </span>
                                            <div class="flex-fill">
                                                <div class="fw-bold text-primary">
                                                    {{ $client['username'] }}
                                                    <span class="mx-2 {{ __($client['clientstatus']['badge']) }}">
                                                        {{ __($client['clientstatus']['name']) }}
                                                    </span>
                                                </div>
                                                <div class="text-muted small">
                                                    <strong>{{ __('client.phone') }}:</strong> {{ $client['phone_number'] }}
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
        @if(!empty($preview))
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-3 text-primary">{{ __('invoice.invoice_preview') }}</h3>
                        <p><strong>{{ __('room.name') }}:</strong> {{ $room['room_name'] }}</p>
                        <p><strong>{{ __('invoice.month') }}:</strong> {{ $preview['month'] }}</p>

                        {{-- ✅ Show active clients --}}
                        @if($clients->isNotEmpty())
                            <div class="mb-3">
                                <strong>{{ __('invoice.current_tenants') }}:</strong>
                                <ul class="mb-0">
                                    @foreach($clients as $client)
                                        <li>
                                            {{ $client['username'] }}
                                            <span class="text-muted small">
                                                ({{ __('client.start_rental_date') }}: {{ $client['start_rental_date'] }})
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-danger"><strong>{{ __('invoice.no_active_tenants') }}</strong></p>
                        @endif

                        {{-- ===== Invoice Details ===== --}}
                        <table class="table table-bordered mt-3">
                            <tbody>
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
                                    <td>{{ number_format($preview['room_rent'], 2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">{{ __('invoice.other_charge') }}</th>
                                    <td>{{ number_format($preview['other_charge'], 2) }}</td>
                                </tr>
                                <tr class="fw-bold">
                                    <th colspan="3" class="text-end">{{ __('invoice.total_amount') }}</th>
                                    <td>{{ number_format($preview['grand_total'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- ===== Print and Save ===== --}}
                        <div class="text-end mt-3 d-print-none">
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="ti ti-printer"></i> {{ __('invoice.print') }}
                            </button>
                        </div>

                        <div class="text-end mt-2 d-print-none">
                            <form method="POST" action="">
                                @csrf
                                @foreach($preview as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <input type="hidden" name="room_id" value="{{ $room['id'] }}">
                                <input type="hidden" name="location_id" value="{{ $room['location']['id'] }}">
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
                                $currentMonth = \Carbon\Carbon::now()->translatedFormat('F Y'); // e.g. "October 2025"
                            @endphp

                            @foreach(['month', 'old_electric', 'new_electric', 'electric_rate', 'old_water', 'new_water', 'water_rate', 'other_charge'] as $field)
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                        {{ __('invoice.' . $field) }}
                                    </label>

                                    {{-- ✅ Apply "monthpicker" only to the month field --}}
                                    @if($field === 'month')
                                        <input type="text" name="month" class="form-control monthpicker"
                                            value="{{ $old['month'] ?? ($preview['month'] ?? $currentMonth) }}"
                                            placeholder="{{ __('invoice.select_month') }}" autocomplete="off">
                                    @else
                                        <input type="number" step="any" name="{{ $field }}" class="form-control"
                                            value="{{ $old[$field] ?? ($preview[$field] ?? '') }}"
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
                            class="btn btn-warning">
                            {{ __('invoice.clear') }}
                        </a>
                        <div>
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
