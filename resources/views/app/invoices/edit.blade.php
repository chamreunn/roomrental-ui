@extends('layouts.app')

@section('content')
    {{-- ===== Generate Invoice Form ===== --}}
    <div class="col-12 d-print-none">
        <div class="card">
            <form method="POST"
                action="{{ route('invoice.update', ['id' => $invoice['id'], 'locationId' => $locationId] ?? '') }}">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    <h4 class="fw-bold mb-3">{{ __('invoice.generate_invoice') }}</h4>

                    <div class="row g-3">
                        @php
                            $old = old();
                            $currentDate = \Carbon\Carbon::now()->format('Y-m-d');
                            $fields = [
                                'month',
                                'old_electric',
                                'new_electric',
                                'electric_rate',
                                'old_water',
                                'new_water',
                                'water_rate',
                                'other_charge',
                            ];
                        @endphp

                        @foreach ($fields as $field)
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                    {{ __('invoice.' . $field) }}
                                </label>

                                @if ($field === 'month')
                                    <input type="text" name="month" class="form-control datepicker"
                                        value="{{ $old['month'] ?? ($invoice['invoice_date'] ?? $currentDate) }}"
                                        placeholder="{{ __('invoice.select_month') }}" autocomplete="off">
                                @else
                                    <input type="number" step="any" name="{{ $field }}" class="form-control"
                                        value="{{ $old[$field] ?? ($invoice[$field] ?? '') }}"
                                        placeholder="{{ __('invoice.' . $field) }}" autocomplete="off">
                                @endif

                                {{-- ✅ Show validation errors --}}
                                @error($field)
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer d-flex align-items-center justify-content-end">
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary">
                            {{ __('invoice.invoice_update') }}
                        </button>
                        <a href="{{ route('invoice.user_index', $locationId) }}" class="btn btn-secondary">
                            {{ __('invoice.cancel') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
