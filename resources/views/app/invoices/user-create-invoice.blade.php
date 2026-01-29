@extends('layouts.app')

@section('content')

    <div class="row g-3">
        {{-- ===== Room Selector ===== --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <label for="roomId" class="form-label required">{{ __('room.select_room') }}</label>

                    <select name="room_id[]" id="roomId" class="form-select tom-select" multiple>
                        @foreach ($rooms as $room)
                            <option value="{{ $room['id'] }}" data-room='@json($room)'
                                data-custom-properties="<span class='{{ $room['status_meta']['badge'] ?? 'badge bg-secondary-lt' }}'>{{ __($room['status_meta']['name'] ?? 'Unknown') }}</span>"
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
        <div class="col-12 d-print-none" id="invoiceContainer"
            style="{{ old('room_id') ? 'display:block;' : 'display:none;' }}">

            <form id="multiInvoiceForm" action="{{ route('invoices.storeMultiple', $locationId) }}" method="POST">
                @csrf

                {{-- ✅ Global Rates (fill once, apply to all rooms) --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <h4 class="fw-bold mb-3">
                            {{ __('invoice.common_rates') ?? 'Common Rates (All Rooms)' }}
                        </h4>

                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label required">{{ __('invoice.electric_rate') }}</label>
                                <input type="text" inputmode="decimal" name="electric_rate" class="form-control js-riel"
                                    value="{{ old('electric_rate') }}" placeholder="{{ __('invoice.electric_rate') }}">
                                @error('electric_rate')
                                    <div class="text-red d-block mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label required">{{ __('invoice.water_rate') }}</label>
                                <input type="text" inputmode="decimal" name="water_rate" class="form-control js-riel"
                                    value="{{ old('water_rate') }}" placeholder="{{ __('invoice.water_rate') }}">
                                @error('water_rate')
                                    <div class="text-red d-block mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div id="formFieldsContainer">
                    @if (old('room_id'))
                        @php
                            // ✅ Use invoice_date (Y-m-d), not month
                            $fields = [
                                'invoice_date',
                                'old_electric',
                                'new_electric',
                                'old_water',
                                'new_water',
                                'other_charge',
                            ];

                            $commaFields = ['other_charge'];
                            $today = \Carbon\Carbon::now()->format('Y-m-d');
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
                                                <label
                                                    class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                                    {{ $field === 'invoice_date' ? __('invoice.invoice_date') ?? 'Invoice Date' : __('invoice.' . $field) }}
                                                </label>

                                                @if ($field === 'invoice_date')
                                                    {{-- ✅ IMPORTANT: name must be invoice_date[] --}}
                                                    <input type="text" name="invoice_date[]"
                                                        class="form-control datepicker"
                                                        value="{{ old('invoice_date.' . $i, $today) }}"
                                                        placeholder="YYYY-MM-DD" autocomplete="off">
                                                @else
                                                    @if (in_array($field, $commaFields))
                                                        <input type="text" inputmode="decimal"
                                                            name="{{ $field }}[]" class="form-control js-riel"
                                                            value="{{ old($field . '.' . $i) }}"
                                                            placeholder="{{ __('invoice.' . $field) }}">
                                                    @else
                                                        <input type="number" step="any" name="{{ $field }}[]"
                                                            class="form-control" value="{{ old($field . '.' . $i) }}"
                                                            placeholder="{{ __('invoice.' . $field) }}">
                                                    @endif
                                                @endif

                                                {{-- ✅ error key must match invoice_date.* --}}
                                                @error(($field === 'invoice_date' ? 'invoice_date' : $field) . '.' . $i)
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
            // ✅ Use invoice_date (Y-m-d), not month
            $fields = ['invoice_date', 'old_electric', 'new_electric', 'old_water', 'new_water', 'other_charge'];
            $commaFields = ['other_charge'];
            $today = \Carbon\Carbon::now()->format('Y-m-d');
        @endphp

        <div class="card mb-3 room-invoice-card">
            <div class="card-body">
                <h4 class="fw-bold mb-3 room-name"></h4>
                <input type="hidden" name="room_id[]" value="">

                <div class="row g-3">
                    @foreach ($fields as $field)
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                {{ $field === 'invoice_date' ? __('invoice.invoice_date') ?? 'Invoice Date' : __('invoice.' . $field) }}
                            </label>

                            @if ($field === 'invoice_date')
                                <input type="text" name="invoice_date[]" class="form-control datepicker"
                                    value="{{ $today }}" placeholder="YYYY-MM-DD" autocomplete="off">
                            @else
                                @if (in_array($field, $commaFields))
                                    <input type="text" inputmode="decimal" name="{{ $field }}[]"
                                        class="form-control js-riel" placeholder="{{ __('invoice.' . $field) }}">
                                @else
                                    <input type="number" step="any" name="{{ $field }}[]" class="form-control"
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const roomSelectEl = document.getElementById('roomId');

                // ✅ TomSelect with badge rendering
                const select = new TomSelect('#roomId', {
                    plugins: ['remove_button'],
                    placeholder: "{{ __('room.please_select_rooms') }}",
                    create: false,

                    render: {
                        option: function(data, escape) {
                            const badge = data.customProperties || '';
                            return `
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="fw-semibold">${escape(data.text)}</span>
                        <span>${badge}</span>
                    </div>
                `;
                        },
                        item: function(data, escape) {
                            const badge = data.customProperties || '';
                            return `
                    <div class="d-flex align-items-center gap-2">
                        <span>${escape(data.text)}</span>
                        <span class="ms-auto">${badge}</span>
                    </div>
                `;
                        }
                    }
                });

                const container = document.getElementById('invoiceContainer');
                const formFieldsContainer = document.getElementById('formFieldsContainer');
                const template = document.getElementById('invoiceTemplate').content;

                // ✅ Date picker: Y-m-d
                function initDatePicker(root = document) {
                    root.querySelectorAll('.datepicker').forEach((el) => {
                        if (el._flatpickr) el._flatpickr.destroy();
                        flatpickr(el, {
                            allowInput: true,
                            dateFormat: "Y-m-d"
                        });
                    });
                }

                // ---------- Comma formatter (supports decimals) ----------
                const cleanNumber = (v) => {
                    v = (v ?? '').toString().replace(/,/g, '').replace(/[^\d.]/g, '');
                    const parts = v.split('.');
                    const intPart = parts[0] || '';
                    const fracPart = parts.length > 1 ? parts.slice(1).join('') : null;
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

                        const sync = () => {
                            el.value = formatNumber(el.value);
                        };
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

                // ✅ Always get correct values from TomSelect
                function getSelectedIds() {
                    const v = select.getValue(); // array for multi, string for single
                    return Array.isArray(v) ? v : (v ? v.split(',').filter(Boolean) : []);
                }

                // Handle room selection
                function rebuildRoomForms() {
                    const ids = getSelectedIds();
                    formFieldsContainer.innerHTML = '';

                    if (ids.length === 0) {
                        container.style.display = 'none';
                        return;
                    }

                    container.style.display = 'block';

                    ids.forEach((id) => {
                        const option = roomSelectEl.querySelector(`option[value="${CSS.escape(id)}"]`);
                        if (!option) return;

                        const room = JSON.parse(option.dataset.room || '{}');
                        const clone = document.importNode(template, true);

                        clone.querySelector('.room-name').textContent =
                            `${room.room_name || ''} — ${room.location?.location_name ?? ''}`;

                        clone.querySelector('input[name="room_id[]"]').value = room.id || id;

                        formFieldsContainer.appendChild(clone);
                    });

                    initDatePicker(formFieldsContainer);
                    initCommaInputs(formFieldsContainer);
                }

                // ✅ attach
                select.on('change', rebuildRoomForms);

                // ✅ init on load
                rebuildRoomForms();
                initDatePicker(document);
                initCommaInputs(document);
            });
        </script>
    @endpush


@endsection
