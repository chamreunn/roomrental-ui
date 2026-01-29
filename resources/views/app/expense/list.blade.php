@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-primary">{{ __('cash_transaction.expense_list') ?? 'Expense Transactions' }}</h3>
        </div>
        <div class="card-body">

            {{-- ✅ Filter Form --}}
            <form method="GET" action="{{ route('expense.list', $id) }}" class="row g-3 mb-3">
                @csrf {{-- required for the POST export button (safe for GET too) --}}

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
                    <label for="category" class="form-label">{{ __('cash_transaction.category_income') }}</label>
                    <select name="category" id="category" class="form-select tom-select">
                        <option value="">{{ __('cash_transaction.select_category') }}</option>
                        @foreach ($category as $key => $label)
                            <option value="{{ $key }}" @selected((string) request('category') === (string) $key)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    {{-- Filter (GET) --}}
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="fas fa-filter"></i> {{ __('cash_transaction.filter') }}
                    </button>

                    {{-- Reset --}}
                    <a href="{{ route('expense.list', $id) }}" class="btn btn-outline-secondary flex-fill">
                        {{ __('cash_transaction.reset') }}
                    </a>

                    {{-- Export (POST) using same inputs --}}
                    <button type="submit" class="btn btn-success flex-fill" formmethod="POST"
                        formaction="{{ route('expense.export', $id) }}">
                        {{ __('cash_transaction.export') ?? 'Export' }}
                    </button>
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
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $expenses->firstItem() + $index }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item['transaction_date'])->translatedFormat('d-M-Y') }}
                                    </td>
                                    <td>{{ $item['category'] }}</td>
                                    <td class="text-end text-danger">{{ number_format($item['amount'], 2) }}(៛)</td>
                                    <td>{{ $item['description'] ?? '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('expense.edit', [$id, $item['id']]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            {{ __('titles.edit') ?? 'Edit' }}
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-danger js-expense-delete-btn"
                                            data-bs-toggle="modal" data-bs-target="#deleteExpenseModal"
                                            data-url="{{ route('expense.destroy', [$id, $item['id']]) }}"
                                            data-date="{{ \Carbon\Carbon::parse($item['transaction_date'])->translatedFormat('d-M-Y') }}"
                                            data-category="{{ $category[$item['category']] ?? $item['category'] }}"
                                            data-amount="{{ number_format($item['amount'], 2) }}">
                                            {{ __('titles.delete') ?? 'Delete' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- ✅ TOTAL ROW --}}
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="3" class="text-end text-danger">
                                    {{ __('cash_transaction.total') ?? 'Total' }}
                                </td>
                                <td class="text-end text-danger">
                                    {{ number_format($totalExpense, 2) }}(៛)
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- ✅ Pagination --}}
                @if ($expenses->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $expenses->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="alert alert-info text-center mb-0 justify-content-center">
                    <x-empty-state title="{{ __('cash_transaction.no_data') }}"
                        message="{{ __('cash_transaction.no_data') }}" svg="svgs/no_result.svg" width="450px" />
                </div>
            @endif
        </div>
    </div>

    @if ($expenses->count() > 0)
        @foreach ($expenses as $index => $item)
            <div class="modal modal-blur fade" id="deleteExpenseModal" tabindex="-1"
                aria-labelledby="deleteExpenseModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteExpenseModalLabel">
                                {{ __('titles.delete') ?? 'Delete' }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-2">
                                {{ __('cash_transaction.confirm_delete') ?? 'Are you sure you want to delete this transaction?' }}
                            </p>

                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">{{ __('cash_transaction.date') ?? 'Date' }}</span>
                                    <strong id="expDelDate">-</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span
                                        class="text-muted">{{ __('cash_transaction.category_expense') ?? 'Category' }}</span>
                                    <strong id="expDelCategory">-</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">{{ __('cash_transaction.amount') ?? 'Amount' }}</span>
                                    <strong id="expDelAmount">-</strong>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3 mb-0">
                                {{ __('cash_transaction.delete_warning') ?? 'This action cannot be undone.' }}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                {{ __('titles.cancel') ?? 'Cancel' }}
                            </button>

                            <form method="POST" id="deleteExpenseForm" action="#">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    {{ __('titles.delete') ?? 'Delete' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deleteExpenseForm');
            const d = document.getElementById('expDelDate');
            const c = document.getElementById('expDelCategory');
            const a = document.getElementById('expDelAmount');

            document.querySelectorAll('.js-expense-delete-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    form.action = btn.dataset.url;
                    d.textContent = btn.dataset.date || '-';
                    c.textContent = btn.dataset.category || '-';
                    a.textContent = btn.dataset.amount || '-';
                });
            });
        });
    </script>
@endpush
