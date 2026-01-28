@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ page_title() }}</h5>
        </div>

        <div class="card-body">
            {{-- Add one transaction --}}
            <form action="{{ route('user_cash_transaction.add_temp') }}" method="POST" id="txForm">
                @csrf

                <div class="row g-3">

                    {{-- Date --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.date') }}</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                            value="{{ old('date', now()->toDateString()) }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Location --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.location') }}</label>
                        <select name="location_id"
                            class="form-select tom-select @error('location_id') is-invalid @enderror">
                            <option value="">{{ __('cash_transaction.select_location') }}</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location['location_id'] }}"
                                    {{ old('location_id') == $location['location_id'] ? 'selected' : '' }}>
                                    {{ $location['location']['location_name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.type') }}</label>
                        <select id="typeSelect" name="type"
                            class="form-select tom-select @error('type') is-invalid @enderror">
                            <option value="">{{ __('cash_transaction.select_type') }}</option>
                            @foreach ($type as $index => $item)
                                <option value="{{ $index }}" {{ old('type') == $index ? 'selected' : '' }}>
                                    {{ __($item) }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div class="col-lg-6">
                        <label id="categoryLabel" class="form-label required">{{ __('cash_transaction.category') }}</label>

                        <select id="categorySelect" name="category"
                            class="form-select tom-select @error('category') is-invalid @enderror">
                            <option value="">{{ __('cash_transaction.select_category') }}</option>
                            @foreach ($category as $index => $item)
                                <option value="{{ $index }}" {{ old('category') == $index ? 'selected' : '' }}>
                                    {{ __($item) }}
                                </option>
                            @endforeach
                        </select>

                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Amount --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.amount') }}</label>

                        {{-- MUST be text to display commas --}}
                        <input type="text" inputmode="decimal" name="amount"
                            class="form-control riel @error('amount') is-invalid @enderror" value="{{ old('amount') }}"
                            autocomplete="off" placeholder="{{ __('cash_transaction.amount') }}">

                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-lg-6">
                        <label class="form-label">{{ __('cash_transaction.note') }}</label>
                        <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="col-lg-12 text-end">
                        <button type="submit" class="btn btn-outline-primary">
                            {{ __('cash_transaction.add_transaction') }}
                            <x-icon name="plus" class="icon-end" />
                        </button>
                    </div>

                </div>
            </form>
        </div>

        {{-- Show temporary transactions --}}
        @if (!empty($transactions))
            <div class="card-body">
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ __('cash_transaction.date') }}</th>
                                <th>{{ __('cash_transaction.location') }}</th>
                                <th>{{ __('cash_transaction.type') }}</th>
                                <th>{{ __('cash_transaction.category') }}</th>
                                <th>{{ __('cash_transaction.amount') }}</th>
                                <th>{{ __('cash_transaction.note') }}</th>
                                <th>{{ __('titles.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $i => $tx)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $tx['date'] }}</td>
                                    <td>{{ $tx['location_name'] }}</td>
                                    <td>{{ $tx['type'] }}</td>
                                    <td>{{ $tx['category'] }}</td>
                                    <td>{{ number_format($tx['amount'], 2) }}</td>
                                    <td>{{ $tx['description'] ?? '-' }}</td>
                                    <td>
                                        <form
                                            action="{{ route('user_cash_transaction.removeTemporary', [$tx['location_id'], $i]) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                {{ __('titles.remove') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form action="{{ route('user_cash_transaction.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        {{ __('cash_transaction.save_all_transactions') }}
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            function initCategoryLabel() {
                const typeSelect = document.getElementById('typeSelect');
                const categorySelect = document.getElementById('categorySelect');
                const categoryLabel = document.getElementById('categoryLabel');

                if (!typeSelect || !categorySelect || !categoryLabel) return;

                const defaultLabel = @json(__('cash_transaction.category'));
                const defaultPlaceholder = @json(__('cash_transaction.select_category'));

                typeSelect.addEventListener('change', function() {
                    const selectedText = typeSelect.options[typeSelect.selectedIndex]?.text?.trim();

                    let newLabel = defaultLabel;
                    let newPlaceholder = defaultPlaceholder;

                    if (typeSelect.value && selectedText) {
                        newLabel = `${defaultLabel} (${selectedText})`;
                        newPlaceholder = `@json(__('cash_transaction.select')) ${selectedText}`;
                    }

                    categoryLabel.textContent = newLabel;

                    if (categorySelect.tomselect) {
                        categorySelect.tomselect.clear(true);
                        categorySelect.tomselect.settings.placeholder = newPlaceholder;
                        categorySelect.tomselect.control_input.setAttribute('placeholder', newPlaceholder);
                        categorySelect.tomselect.refreshOptions(false);
                    } else {
                        categorySelect.options[0].textContent = newPlaceholder;
                    }
                });
            }

            // ----- Riel comma formatter (decimals supported) -----
            const cleanNumber = (v) => {
                v = (v ?? '').toString().replace(/,/g, '').replace(/[^\d.]/g, '');
                const parts = v.split('.');
                const intPart = parts[0] || '';
                const fracPart = parts.length > 1 ? parts.slice(1).join('') : null;
                return fracPart !== null ? `${intPart}.${fracPart}` : intPart;
            };

            const addCommas = (s) => s.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            const formatNumber = (v) => {
                const cleaned = cleanNumber(v);
                if (!cleaned) return '';
                const [i, f] = cleaned.split('.');
                const fi = i ? addCommas(i) : '';
                return (f !== undefined) ? `${fi}.${f}` : fi;
            };

            function initRielInputs(root = document) {
                root.querySelectorAll('input.riel').forEach((el) => {
                    if (el.dataset.rielBound === '1') return;
                    el.dataset.rielBound = '1';

                    const sync = () => {
                        el.value = formatNumber(el.value);
                    };

                    el.addEventListener('input', sync);
                    el.addEventListener('blur', sync);
                    el.addEventListener('paste', () => setTimeout(sync, 0));

                    sync();
                });

                root.querySelectorAll('form').forEach((form) => {
                    if (form.dataset.rielSubmitBound === '1') return;
                    form.dataset.rielSubmitBound = '1';

                    form.addEventListener('submit', () => {
                        form.querySelectorAll('input.riel').forEach((el) => {
                            el.value = cleanNumber(el.value); // send clean number to backend
                        });
                    });
                });
            }

            function boot() {
                initCategoryLabel();
                initRielInputs(document);
                // debug: should be >= 1
                // console.log('riel inputs:', document.querySelectorAll('input.riel').length);
            }

            document.addEventListener('DOMContentLoaded', boot);
            document.addEventListener('turbo:load', boot); // if Turbo is used
            document.addEventListener('livewire:navigated', boot); // if Livewire navigation is used
        })();
    </script>
@endpush
