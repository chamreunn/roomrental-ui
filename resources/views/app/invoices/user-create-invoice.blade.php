@extends('layouts.app')

@section('content')

    <div class="row g-3">
        {{-- ===== Room Selector ===== --}}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <label for="roomId" class="form-label required">{{ __('room.select_room') }}</label>
                    <select name="roomId[]" id="roomId" class="form-select" multiple>
                        <option value="">{{ __('room.please_select_rooms') }}</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room['id'] }}" data-room='@json($room)'
                                @if (is_array(old('room_id')) && in_array($room['id'], old('room_id'))) selected @endif>
                                {{ $room['room_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ===== Multi-Form Container ===== --}}
        <div class="col-12 d-print-none" id="invoiceContainer"
            @if (old('room_id')) style="display:block;" @else
        style="display:none;" @endif>
            <form id="multiInvoiceForm" action="{{ route('invoices.storeMultiple', $locationId) }}" method="POST">
                @csrf
                <div id="formFieldsContainer">
                    @if (old('room_id'))
                        @foreach (old('room_id') as $i => $roomId)
                            @php
                                $room = collect($rooms)->firstWhere('id', $roomId);
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
                                                    <input type="text" name="month[]"
                                                        class="form-control datepicker value="
                                                        {{ old("
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    month.$i") }}"
                                                        placeholder="{{ __('invoice.select_month') }}" autocomplete="off">
                                                @else
                                                    <input type="number" step="any" name="{{ $field }}[]"
                                                        class="form-control value=" {{ old(" $field.$i") }}"
                                                        placeholder="{{ __('invoice.' . $field) }}">
                                                @endif

                                                @error("$field.$i")
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

    {{-- ===== Template for new room form (used when selecting rooms dynamically) ===== --}}
    <template id="invoiceTemplate">
        <div class="card mb-3 room-invoice-card">
            <div class="card-body">
                <h4 class="fw-bold mb-3 room-name"></h4>
                <input type="hidden" name="room_id[]" value="">
                <div class="row g-3">
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
                        $currentMonth = \Carbon\Carbon::now()->format('Y-m');
                    @endphp
                    @foreach ($fields as $field)
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label {{ $field !== 'other_charge' ? 'required' : '' }}">
                                {{ __('invoice.' . $field) }}
                            </label>

                            @if ($field === 'month')
                                <input type="text" name="month[]" class="form-control datepicker"
                                    value="{{ $currentMonth }}" placeholder="{{ __('invoice.select_month') }}"
                                    autocomplete="off">
                            @else
                                <input type="number" step="any" name="{{ $field }}[]" class="form-control"
                                    placeholder="{{ __('invoice.' . $field) }}">
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
            document.addEventListener('DOMContentLoaded', function() {
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
                const customMonths = monthsTranslation[appLocale] || monthsTranslation['en'];

                // ✅ Flatpickr locale override
                const flatpickrLocale = {
                    months: {
                        shorthand: customMonths,
                        longhand: customMonths,
                    },
                    weekdays: {
                        shorthand: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
                        longhand: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    }
                };

                function initDatePicker() {
                    flatpickr(".datepicker", {
                        locale: flatpickrLocale,
                        dateFormat: "Y-m-d", // full date format
                        altFormat: "F j, Y", // optional friendly display
                        allowInput: true,
                    });
                }

                // Handle room selection
                select.on('change', function(values) {
                    formFieldsContainer.innerHTML = '';

                    if (values.length === 0) {
                        container.style.display = 'none';
                        return;
                    }

                    container.style.display = 'block';

                    values.forEach(id => {
                        const option = select.input.querySelector(`option[value="${id}"]`);
                        const room = JSON.parse(option.dataset.room);
                        const clone = document.importNode(template, true);

                        clone.querySelector('.room-name').textContent =
                            `${room.room_name} — ${room.location?.location_name ?? ''}`;
                        clone.querySelector('input[name="room_id[]"]').value = room.id;

                        formFieldsContainer.appendChild(clone);
                    });

                    // Initialize Flatpickr for all new inputs
                    initDatePicker();
                });

                // Initialize on page load for old inputs (after validation errors)
                initDatePicker();
            });
        </script>
    @endpush

@endsection
