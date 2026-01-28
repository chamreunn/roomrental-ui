@extends('layouts.app')

@section('content')

   <div class="row g-3">
    {{-- ===== Room Selector ===== --}}
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <label for="roomId" class="form-label required">{{ __('room.select_room') }}</label>

                <select name="room_id[]" id="roomId" class="form-select" multiple>
                    @foreach ($rooms as $room)
                        <option value="{{ $room['id'] }}"
                                data-room='@json($room)'
                                @selected(in_array($room['id'], old('room_id', [])))>
                            {{ $room['room_name'] }}
                        </option>
                    @endforeach
                </select>

                @error('room_id')
                    <div class="text-red mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    {{-- ===== Multi-Form Container ===== --}}
    <div class="col-12 d-print-none" id="invoiceContainer" style="{{ old('room_id') ? 'display:block;' : 'display:none;' }}">
        <form id="multiInvoiceForm" action="{{ route('invoices.storeMultiple', $locationId) }}" method="POST">
            @csrf

            <div id="formFieldsContainer">
                @if (old('room_id'))
                    @php
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

                        $commaFields = ['electric_rate', 'water_rate', 'other_charge'];
                    @endphp

                    @foreach (old('room_id') as $i => $roomId)
                        @php
                            $room = collect($rooms)->firstWhere('id', $roomId);
                        @endphp

                        <div class="card mb-3 room-invoice-card">
                            <div class="card-body">
                                <h4 class="fw-bold mb-3 room-name">
                                    {{ $room['room_name'] ?? 'Unknown Room' }} —
                                    {{ $room['location']['location_name'] ?? '' }}
                                </h4>

                                <input type="hidden" name="room_id[]" value="{{ $roomId }}">

                                <div class="row g-3">
                                    @foreach ($fields as $field)
                                        <div class="col-lg-3 col-md-6">
                                            <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                                {{ __('invoice.' . $field) }}
                                            </label>

                                            @if ($field === 'month')
                                                <input type="text"
                                                       name="month[]"
                                                       class="form-control datepicker"
                                                       value="{{ old('month.' . $i) }}"
                                                       placeholder="{{ __('invoice.select_month') }}"
                                                       autocomplete="off">
                                            @else
                                                @if (in_array($field, $commaFields))
                                                    <input type="text"
                                                           inputmode="decimal"
                                                           name="{{ $field }}[]"
                                                           class="form-control js-riel"
                                                           value="{{ old($field . '.' . $i) }}"
                                                           placeholder="{{ __('invoice.' . $field) }}">
                                                @else
                                                    <input type="number"
                                                           step="any"
                                                           name="{{ $field }}[]"
                                                           class="form-control"
                                                           value="{{ old($field . '.' . $i) }}"
                                                           placeholder="{{ __('invoice.' . $field) }}">
                                                @endif
                                            @endif

                                            @error($field . '.' . $i)
                                                <div class="text-red d-block mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="card-body text-end">
                <button type="submit" class="btn btn-primary">
                    💾 {{ __('invoice.save_all') }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== Template for new room form ===== --}}
<template id="invoiceTemplate">
    @php
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

        $commaFields = ['electric_rate', 'water_rate', 'other_charge'];
        $currentMonth = \Carbon\Carbon::now()->format('Y-m');
    @endphp

    <div class="card mb-3 room-invoice-card">
        <div class="card-body">
            <h4 class="fw-bold mb-3 room-name"></h4>
            <input type="hidden" name="room_id[]" value="">

            <div class="row g-3">
                @foreach ($fields as $field)
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                            {{ __('invoice.' . $field) }}
                        </label>

                        @if ($field === 'month')
                            <input type="text"
                                   name="month[]"
                                   class="form-control datepicker"
                                   value="{{ $currentMonth }}"
                                   placeholder="{{ __('invoice.select_month') }}"
                                   autocomplete="off">
                        @else
                            @if (in_array($field, $commaFields))
                                <input type="text"
                                       inputmode="decimal"
                                       name="{{ $field }}[]"
                                       class="form-control js-riel"
                                       placeholder="{{ __('invoice.' . $field) }}">
                            @else
                                <input type="number"
                                       step="any"
                                       name="{{ $field }}[]"
                                       class="form-control"
                                       placeholder="{{ __('invoice.' . $field) }}">
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</template>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomSelectEl = document.getElementById('roomId');

            const select = new TomSelect('#roomId', {
                plugins: ['remove_button'],
                placeholder: "{{ __('room.please_select_rooms') }}",
            });

            const container = document.getElementById('invoiceContainer');
            const formFieldsContainer = document.getElementById('formFieldsContainer');
            const template = document.getElementById('invoiceTemplate').content;

            // ✅ Get language and month translations from layout
            const appLocale = window.appLocale || 'en';
            const monthsTranslation = window.monthsTranslation || {};
            const customMonths = monthsTranslation[appLocale] || monthsTranslation['en'] || [];

            const flatpickrLocale = {
                months: { shorthand: customMonths, longhand: customMonths },
            };

            // ---------- Month picker ----------
            function initDatePicker(root = document) {
                root.querySelectorAll('.datepicker').forEach((el) => {
                    if (el._flatpickr) el._flatpickr.destroy();

                    flatpickr(el, {
                        locale: flatpickrLocale,
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

            // ---------- Comma formatter (supports decimals) ----------
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

            // Before submit: remove commas so backend gets clean numbers
            const form = document.getElementById('multiInvoiceForm');
            if (form) {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input.js-riel').forEach((el) => {
                        el.value = cleanNumber(el.value);
                    });
                });
            }

            // Handle room selection
            select.on('change', function (values) {
                // TomSelect can give string "1,2,3" or array depending on config
                const ids = Array.isArray(values)
                    ? values
                    : (values ? values.split(',').filter(Boolean) : []);

                formFieldsContainer.innerHTML = '';

                if (ids.length === 0) {
                    container.style.display = 'none';
                    return;
                }

                container.style.display = 'block';

                ids.forEach((id) => {
                    const option = roomSelectEl.querySelector(`option[value="${id}"]`);
                    if (!option) return;

                    const room = JSON.parse(option.dataset.room || '{}');
                    const clone = document.importNode(template, true);

                    clone.querySelector('.room-name').textContent =
                        `${room.room_name || ''} — ${room.location?.location_name ?? ''}`;

                    clone.querySelector('input[name="room_id[]"]').value = room.id || id;

                    formFieldsContainer.appendChild(clone);
                });

                // init for newly added fields
                initDatePicker(formFieldsContainer);
                initCommaInputs(formFieldsContainer);
            });

            // Initialize on page load (old inputs after validation errors)
            initDatePicker(document);
            initCommaInputs(document);
        });
    </script>
@endpush


@endsection
