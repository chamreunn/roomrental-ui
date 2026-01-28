@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title text-primary mb-0">
                {{ __('cash_transaction.income_list') ?? 'Income Transactions' }}
            </h3>
        </div>

        <div class="card-body">
            {{-- ✅ ONE FORM ONLY --}}
            <form method="GET" action="{{ route('income.list', $id) }}" class="row g-3 mb-3" id="filterForm">
                @csrf {{-- ✅ put CSRF inside the form (safe even for GET) --}}

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
                    <a href="{{ route('income.list', $id) }}" class="btn btn-outline-secondary flex-fill">
                        {{ __('cash_transaction.reset') }}
                    </a>

                    {{-- Export (POST) using same inputs --}}
                    <button type="submit" class="btn btn-success flex-fill" formmethod="POST"
                        formaction="{{ route('income.export', $id) }}">
                        {{ __('cash_transaction.export') ?? 'Export' }}
                    </button>
                </div>
            </form>

            {{-- ✅ Table --}}
            @if ($incomes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-vcentered">
                        <thead>
                            <tr>
                                <th class="text-center text-primary" style="width: 50px;">#</th>
                                <th>{{ __('cash_transaction.date') ?? 'Date' }}</th>
                                <th>{{ __('cash_transaction.category_income') ?? 'Category' }}</th>
                                <th class="text-end">{{ __('cash_transaction.amount') ?? 'Amount' }}</th>
                                <th>{{ __('cash_transaction.note') ?? 'Note' }}</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($incomes as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $incomes->firstItem() + $index }}</td>
                                    <td>{{ \Carbon\Carbon::parse($item['transaction_date'])->translatedFormat('d-M-Y') }}
                                    </td>
                                    <td>{{ $item['category'] }}</td>
                                    <td class="text-end text-primary">{{ number_format($item['amount'], 2) }}</td>
                                    <td>{{ $item['description'] ?? '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('income.edit', [$id, $item['id']]) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            {{ __('titles.edit') ?? 'Edit' }}
                                        </a>

                                        <button type="button" class="btn btn-sm btn-outline-danger js-delete-btn"
                                            data-bs-toggle="modal" data-bs-target="#deleteIncomeModal"
                                            data-url="{{ route('income.destroy', [$id, $item['id']]) }}"
                                            data-date="{{ \Carbon\Carbon::parse($item['transaction_date'])->translatedFormat('d-M-Y') }}"
                                            data-category="{{ $item['category'] }}"
                                            data-amount="{{ number_format($item['amount'], 2) }}">
                                            {{ __('titles.delete') ?? 'Delete' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="3" class="text-end text-danger">
                                    {{ __('cash_transaction.total') ?? 'Total' }}
                                </td>
                                <td class="text-end text-danger">{{ number_format($totalIncome, 2) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($incomes->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $incomes->appends(request()->query())->links('pagination::bootstrap-5') }}
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
@endsection

<!-- Delete Modal -->
@foreach ($incomes as $index => $item)
    <div class="modal modal-blur fade" id="deleteIncomeModal" tabindex="-1" aria-labelledby="deleteIncomeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteIncomeModalLabel">
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
                            <strong id="delDate">-</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('cash_transaction.category_income') ?? 'Category' }}</span>
                            <strong id="delCategory">-</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">{{ __('cash_transaction.amount') ?? 'Amount' }}</span>
                            <strong id="delAmount">-</strong>
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

                    <form method="POST" id="deleteIncomeForm" action="#">
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deleteIncomeForm');
            const delDate = document.getElementById('delDate');
            const delCategory = document.getElementById('delCategory');
            const delAmount = document.getElementById('delAmount');

            document.querySelectorAll('.js-delete-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    form.action = btn.dataset.url;
                    delDate.textContent = btn.dataset.date || '-';
                    delCategory.textContent = btn.dataset.category || '-';
                    delAmount.textContent = btn.dataset.amount || '-';
                });
            });
        });
    </script>
@endpush
