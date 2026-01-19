<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class UserExpenseController extends Controller
{
    public function index()
    {
        $locations = collect(Session::get('user.user_locations', []))
            ->pluck('location')
            ->values()
            ->toArray();

        return view('app.user_expense.index', compact('locations'));
    }

    public function list($id)
    {
        $buttons = [
            [
                'text'  => __('titles.back'),
                'icon'  => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url'   => route('expense.index', $id),
            ],
        ];

        $type = $this->CashTransactionType()::EXPENSE;
        $page = request('page', 1);

        $filters = [
            'type' => $type,
            'page' => $page,
        ];

        // ===============================
        // FETCH FROM API
        // ===============================
        $response = $this->api()
            ->withHeaders(['Location-Id' => $id]) // ✅ keep consistent
            ->get('v1/cash-transactions', $filters);

        // ===============================
        // API DATA
        // ===============================
        $data        = $response['data']['data'] ?? [];
        $perPage     = $response['data']['per_page'] ?? 10;
        $currentPage = $response['data']['current_page'] ?? $page;

        // Optional: API totals (if needed later)
        $apiTotals = collect($response['totals'] ?? []);

        // ===============================
        // LOCAL FILTERING
        // ===============================
        $collection = collect($data);

        // From Date
        if (request()->filled('from_date')) {
            $collection = $collection->filter(
                fn($item) =>
                $item['transaction_date'] >= request('from_date')
            );
        }

        // To Date
        if (request()->filled('to_date')) {
            $collection = $collection->filter(
                fn($item) =>
                $item['transaction_date'] <= request('to_date')
            );
        }

        // Category
        if (request()->filled('category')) {
            $collection = $collection->filter(
                fn($item) =>
                str_contains(
                    mb_strtolower($item['category']),
                    mb_strtolower(request('category'))
                )
            );
        }

        // ===============================
        // CALCULATE TOTAL EXPENSE
        // ===============================
        $totalExpense = $collection->sum(fn($item) => (float) $item['amount']);

        // ===============================
        // PAGINATION
        // ===============================
        $filteredData = $collection->values();
        $totalRows   = $filteredData->count();

        $expenses = new LengthAwarePaginator(
            $filteredData->forPage($currentPage, $perPage),
            $totalRows,
            $perPage,
            $currentPage,
            [
                'path'  => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('app.user_expense.list', compact(
            'expenses',
            'buttons',
            'id',
            'totalExpense',
            'apiTotals'
        ));
    }
}
