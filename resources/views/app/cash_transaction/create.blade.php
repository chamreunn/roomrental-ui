@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">{{ page_title() }}</h5>
        </div>

        <div class="card-body">
            {{-- Add one transaction --}}
            <form action="{{ route('cash_transaction.add_temp', $location_id) }}" method="POST" id="txForm">
                @csrf

                <div class="row g-3">
                    {{-- Date --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.date') }}</label>
                        <input type="date"
                               name="date"
                               class="form-control datepicker @error('date') is-invalid @enderror"
                               value="{{ old('date', date('Y-m-d')) }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.type') }}</label>
                        <select name="type" class="form-select tom-select @error('type') is-invalid @enderror">
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
                        <label class="form-label required">{{ __('cash_transaction.category') }}</label>
                        <select name="category" class="form-select tom-select @error('category') is-invalid @enderror">
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

                    {{-- Amount (✅ comma formatted) --}}
                    <div class="col-lg-6">
                        <label class="form-label required">{{ __('cash_transaction.amount') }}</label>

                        {{-- MUST be text to show commas --}}
                        <input type="text"
                               inputmode="decimal"
                               name="amount"
                               class="form-control riel @error('amount') is-invalid @enderror"
                               value="{{ old('amount') }}"
                               placeholder="{{ __('cash_transaction.amount') }}"
                               autocomplete="off">

                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div class="col-lg-12">
                        <label class="form-label">{{ __('cash_transaction.note') }}</label>
                        <textarea name="description" rows="2"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Add button --}}
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
                                    <td>{{ $tx['type'] }}</td>
                                    <td>{{ $tx['category'] }}</td>
                                    <td>{{ number_format($tx['amount'], 2) }}</td>
                                    <td>{{ $tx['description'] ?? '-' }}</td>
                                    <td>
                                        <form action="{{ route('cash_transaction.removeTemporary', [$location_id, $i]) }}" method="POST">
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

                <form action="{{ route('cash_transaction.store', $location_id) }}" method="POST">
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
document.addEventListener('DOMContentLoaded', () => {

  // digits + one dot (supports decimals)
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

  document.querySelectorAll('input.riel').forEach((el) => {
    if (el.dataset.rielBound === '1') return;
    el.dataset.rielBound = '1';

    const sync = () => { el.value = formatNumber(el.value); };

    el.addEventListener('input', sync);
    el.addEventListener('blur', sync);
    el.addEventListener('paste', () => setTimeout(sync, 0));
    sync();
  });

  // remove commas before submit
  const form = document.getElementById('txForm');
  if (form) {
    form.addEventListener('submit', () => {
      form.querySelectorAll('input.riel').forEach((el) => {
        el.value = cleanNumber(el.value);
      });
    });
  }
});
</script>
@endpush
