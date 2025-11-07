<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseController extends Controller
{
    public function index()
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.expense.index', compact('locations'));
    }

    public function list($id)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('expense.index', $id),
            ],
        ];

        $type = $this->CashTransactionType()::EXPENSE;
        $page = request('page', 1);

        // Base API request
        $response = $this->api()
            ->withHeaders(['location_id' => $id])
            ->get('v1/cash-transactions', [
                'type' => $type,
                'page' => $page,
            ]);

        // Handle missing or null response
        $data = $response['data']['data'] ?? [];
        $total = $response['data']['total'] ?? 0;
        $perPage = $response['data']['per_page'] ?? 10;
        $currentPage = $response['data']['current_page'] ?? $page;

        // Convert to collection for local filtering
        $collection = collect($data);

        // ✅ Local filters (since API doesn’t support these)
        if (request()->filled('from_date')) {
            $collection = $collection->filter(function ($item) {
                return $item['transaction_date'] >= request('from_date');
            });
        }

        if (request()->filled('to_date')) {
            $collection = $collection->filter(function ($item) {
                return $item['transaction_date'] <= request('to_date');
            });
        }

        if (request()->filled('category')) {
            $collection = $collection->filter(function ($item) {
                return str_contains(
                    mb_strtolower($item['category']),
                    mb_strtolower(request('category'))
                );
            });
        }

        // Rebuild filtered collection
        $filteredData = $collection->values();
        $total = $filteredData->count();

        // Manual pagination
        $expenses = new LengthAwarePaginator(
            $filteredData->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('app.expense.list', compact('expenses', 'buttons', 'id'));
    }
}
