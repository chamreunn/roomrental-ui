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

        // Base params for API
        $filters = [
            'type' => $type,
            'page' => $page,
        ];

        // ✅ Fetch all data (no date filter in API)
        $response = $this->api()
            ->withHeaders(['location_id' => $id])
            ->get('v1/cash-transactions', $filters);

        $data = $response['data']['data'] ?? [];
        $total = $response['data']['total'] ?? 0;
        $perPage = $response['data']['per_page'] ?? 10;
        $currentPage = $response['data']['current_page'] ?? $page;

        // ✅ Convert to collection for local filtering
        $collection = collect($data);

        // 🧠 Apply filters locally (in Laravel)
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

        // ✅ Reset keys and count
        $filteredData = $collection->values();
        $total = $filteredData->count();

        // ✅ Manually paginate filtered results
        $incomes = new LengthAwarePaginator(
            $filteredData->forPage($currentPage, $perPage),
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('app.user_income.list', compact('incomes', 'buttons', 'id'));
    }
}
