<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class UserIncomeController extends Controller
{
    public function index()
    {
        $locations = collect(Session::get('user.user_locations', []))
            ->pluck('location')
            ->values()
            ->toArray();

        return view('app.user_income.index', compact('locations'));
    }

    public function list($id)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('income.index', $id),
            ],
        ];

        $type = $this->CashTransactionType()::INCOME;
        $page = request('page', 1);

        $filters = [
            'type' => $type,
            'page' => $page,
        ];

        // ✅ Fetch from API
        $response = $this->api()
            ->withHeaders(['Location-Id' => $id])
            ->get('v1/cash-transactions', $filters);

        // ===============================
        // API DATA
        // ===============================
        $data        = $response['data']['data'] ?? [];
        $perPage     = $response['data']['per_page'] ?? 10;
        $currentPage = $response['data']['current_page'] ?? $page;

        // ✅ API TOTALS (before filter)
        $apiTotals = collect($response['totals'] ?? []);

        // ===============================
        // LOCAL FILTERING
        // ===============================
        $collection = collect($data);

        if (request()->filled('from_date')) {
            $collection = $collection->filter(
                fn($item) =>
                $item['transaction_date'] >= request('from_date')
            );
        }

        if (request()->filled('to_date')) {
            $collection = $collection->filter(
                fn($item) =>
                $item['transaction_date'] <= request('to_date')
            );
        }

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
        // CALCULATE TOTAL AFTER FILTER
        // ===============================
        $totalIncome = $collection->sum(fn($item) => (float) $item['amount']);

        // ===============================
        // PAGINATION
        // ===============================
        $filteredData = $collection->values();
        $totalRows = $filteredData->count();

        $incomes = new LengthAwarePaginator(
            $filteredData->forPage($currentPage, $perPage),
            $totalRows,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('app.user_income.list', compact(
            'incomes',
            'buttons',
            'id',
            'totalIncome',
            'apiTotals'
        ));
    }
}
