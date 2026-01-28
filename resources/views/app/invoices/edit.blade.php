@extends('layouts.app')

@section('content')
    {{-- ===== Generate Invoice Form ===== --}}
    <div class="col-12 d-print-none">
        <div class="card">
            <form id="invoiceForm"
                  method="POST"
                  action="{{ route('invoice.update', ['id' => $invoice['id'], 'locationId' => $locationId]) }}">
                @csrf
                @method('PATCH')

                <div class="card-body">
                    <h4 class="fw-bold mb-3">{{ __('invoice.generate_invoice') }}</h4>

                    <div class="row g-3">
                        @php
                            $old = old();
                            $currentMonth = \Carbon\Carbon::now()->format('Y-m');

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

                            // fields that should show comma formatting
                            $commaFields = ['electric_rate', 'water_rate', 'other_charge'];
                        @endphp

                        @foreach ($fields as $field)
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                    {{ __('invoice.' . $field) }}
                                </label>

                                @if ($field === 'month')
                                    <input type="text"
                                           name="month"
                                           class="form-control datepicker"
                                           value="{{ $old['month'] ?? ($invoice['month'] ?? $currentMonth) }}"
                                           placeholder="{{ __('invoice.select_month') }}"
                                           autocomplete="off">
                                @else
                                    @if (in_array($field, $commaFields))
                                        {{-- Use text to allow commas --}}
                                        <input type="text"
                                               inputmode="decimal"
                                               name="{{ $field }}"
                                               class="form-control js-riel"
                                               value="{{ $old[$field] ?? ($invoice[$field] ?? '') }}"
                                               placeholder="{{ __('invoice.' . $field) }}"
                                               autocomplete="off">
                                    @else
                                        <input type="number"
                                               step="any"
                                               name="{{ $field }}"
                                               class="form-control"
                                               value="{{ $old[$field] ?? ($invoice[$field] ?? '') }}"
                                               placeholder="{{ __('invoice.' . $field) }}"
                                               autocomplete="off">
                                    @endif
                                @endif

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

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // -------- Month picker (Y-m) --------
            function initMonthPicker() {
                document.querySelectorAll('.datepicker').forEach((el) => {
                    if (el._flatpickr) el._flatpickr.destroy();

                    flatpickr(el, {
                        allowInput: true,
                        plugins: [
                            new monthSelectPlugin({
                                shorthand: true,
                                dateFormat: "Y-m",
                                altFormat: "F Y",
                            })
                        ],
                    });
                });
            }

            // -------- Comma formatter (supports decimals) --------
            const cleanNumber = (v) => {
                v = (v ?? '').toString().replace(/,/g, '').replace(/[^\d.]/g, '');
                const parts = v.split('.');
                const intPart = parts[0] || '';
                const fracPart = parts.length > 1 ? parts.slice(1).join('') : null; // remove extra dots
                return fracPart !== null ? `${intPart}.${fracPart}` : intPart;
            };

            const addCommas = (intStr) => intStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            const formatNumber = (v) => {
                const cleaned = cleanNumber(v);
                if (!cleaned) return '';

                const [intPart, fracPart] = cleaned.split('.');
                const formattedInt = intPart ? addCommas(intPart) : '';

                return (fracPart !== undefined) ? `${formattedInt}.${fracPart}` : formattedInt;
            };

            function initCommaInputs(root = document) {
                root.querySelectorAll('input.js-riel').forEach((el) => {
                    if (el.dataset.bound === '1') return;
                    el.dataset.bound = '1';

                    const sync = () => { el.value = formatNumber(el.value); };

                    el.addEventListener('input', sync);
                    el.addEventListener('blur', sync);
                    el.addEventListener('paste', () => setTimeout(sync, 0));
                    sync();
                });
            }

            // Strip commas before submit
            const form = document.getElementById('invoiceForm');
            if (form) {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input.js-riel').forEach((el) => {
                        el.value = cleanNumber(el.value);
                    });
                });
            }

            initMonthPicker();
            initCommaInputs(document);
        });
    </script>
@endpush
