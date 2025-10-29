@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body">
        <h3 class="mb-4 text-primary">{{ __('cash_transaction.income_list') ?? 'Income Transactions' }}</h3>

        @php
            $pagination = $incomes['data'] ?? [];
            $items = $pagination['data'] ?? [];
            $totals = $incomes['totals'][0] ?? null;
        @endphp

        {{-- ✅ Check if data exists --}}
        @if (!empty($items))
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>{{ __('cash_transaction.date') ?? 'Date' }}</th>
                            <th>{{ __('cash_transaction.category') ?? 'Category' }}</th>
                            <th class="text-end">{{ __('cash_transaction.amount') ?? 'Amount' }}</th>
                            <th>{{ __('cash_transaction.note') ?? 'Note' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($item['transaction_date'])->format('d/m/Y') }}</td>
                                <td>{{ $item['category'] }}</td>
                                <td class="text-end">{{ number_format($item['amount'], 2) }}</td>
                                <td>{{ $item['description'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                    {{-- ✅ Totals Row --}}
                    @if ($totals)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">{{ __('cash_transaction.total') ?? 'Total' }}</th>
                                <th>{{ $totals['category'] }}</th>
                                <th class="text-end text-success fw-bold">{{ number_format($totals['total'], 2) }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- ✅ Pagination --}}
            @if (!empty($pagination['links']))
                <nav>
                    <ul class="pagination justify-content-center">
                        @foreach ($pagination['links'] as $link)
                            <li class="page-item {{ $link['active'] ? 'active' : '' }} {{ !$link['url'] ? 'disabled' : '' }}">
                                @if ($link['url'])
                                    <a class="page-link" href="{{ $link['url'] }}">{!! $link['label'] !!}</a>
                                @else
                                    <span class="page-link">{!! $link['label'] !!}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif

        @else
            {{-- No Data --}}
            <div class="alert alert-info text-center mb-0">
                {{ __('cash_transaction.no_data') ?? 'No income records found.' }}
            </div>
        @endif
    </div>
</div>
@endsection
