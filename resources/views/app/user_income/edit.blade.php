@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-primary mb-0">
                {{ __('titles.edit') ?? 'Edit Income' }}
            </h3>
        </div>

        <form method="POST" action="{{ route('user_income.update', [$id, $txId]) }}">
            @csrf
            @method('PATCH')

            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required">{{ __('cash_transaction.date') }}</label>
                        <input type="date" name="transaction_date" class="form-control datepicker"
                            value="{{ old('transaction_date', $income['transaction_date'] ?? '') }}">
                        @error('transaction_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">{{ __('cash_transaction.category_income') }}</label>
                        <select name="category" class="form-select tom-select">
                            <option value="">{{ __('cash_transaction.select_category') }}</option>

                            @foreach ($category as $key => $label)
                                <option value="{{ $key }}" @selected((string) old('category', $selectedCategoryKey) === (string) $key)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label required">{{ __('cash_transaction.amount') }}</label>
                        <input type="number" step="0.01" name="amount" class="form-control"
                            value="{{ old('amount', $income['amount'] ?? '') }}">
                        @error('amount')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ __('cash_transaction.note') }}</label>
                        <textarea name="description" rows="2" class="form-control">{{ old('description', $income['description'] ?? '') }}</textarea>
                        @error('description')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('user_income.list', $id) }}" class="btn btn-outline-secondary">
                    {{ __('titles.back') ?? 'Back' }}
                </a>
                <button class="btn btn-primary" type="submit">
                    {{ __('titles.save') ?? 'Save' }}
                </button>
            </div>
        </form>
    </div>
@endsection
