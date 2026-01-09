@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-primary">{{ __('cash_transaction.expense_list') ?? 'Expense Transactions' }}</h3>
        </div>
        <div class="card-body">

            {{-- ✅ Filter Form --}}
            <form method="GET" action="{{ route('user_expense.list', $id) }}" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">{{ __('cash_transaction.from_date') }}</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><x-icon name="calendar-week" /></span>
                        <input type="date" name="from_date" id="from_date" class="form-control datepicker"
                            value="{{ request('from_date') }}" placeholder="{{ __('cash_transaction.select_date') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="to_date" class="form-label">{{ __('cash_transaction.to_date') }}</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><x-icon name="calendar-week" /></span>
                        <input type="date" name="to_date" id="to_date" class="form-control datepicker"
                            value="{{ request('to_date') }}" placeholder="{{ __('cash_transaction.select_date') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="category" class="form-label">{{ __('cash_transaction.category_expense') }}</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><x-icon name="category" /></span>
                        <input type="text" name="category" id="category" class="form-control"
                            placeholder="{{ __('cash_transaction.search_category') }}" value="{{ request('category') }}">
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2 w-100">
                        <i class="fas fa-filter"></i> {{ __('cash_transaction.filter') }}
                    </button>
                    <a href="{{ route('user_expense.list', $id) }}" class="btn btn-outline-secondary w-100">
                        {{ __('cash_transaction.reset') }}
                    </a>
                </div>
            </form>

            {{-- ✅ Table --}}
            @if ($expenses->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcentered">
                        <thead>
                            <tr>
                                <th class="text-center text-primary" style="width: 50px;">#</th>
                                <th>{{ __('cash_transaction.date') ?? 'Date' }}</th>
                                <th>{{ __('cash_transaction.category_expense') ?? 'Category' }}</th>
                                <th class="text-end">{{ __('cash_transaction.amount') ?? 'Amount' }}</th>
                                <th>{{ __('cash_transaction.note') ?? 'Note' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $expenses->firstItem() + $index }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item['transaction_date'])->translatedFormat('d-M-Y') }}</td>
                                    <td>{{ $item['category'] }}</td>
                                    <td class="text-end text-danger">{{ number_format($item['amount'], 2) }}</td>
                                    <td>{{ $item['description'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ✅ Pagination --}}
                @if ($expenses->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expenses->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="alert alert-info text-center mb-0">
                    <x-empty-state title="{{ __('cash_transaction.no_data') }}" message="{{ __('cash_transaction.no_data') }}"
                        svg="svgs/no_result.svg" width="450px" />
                </div>
            @endif
        </div>
    </div>
@endsection
