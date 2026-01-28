<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\Response as HttpResponse;

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
        // categories: [1=>"ថ្លៃជួលបន្ទប់", 2=>"ថ្លៃអគ្គិសនី", ...]
        $category = $this->CashTransactionCategory()->getCategories();
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

        // ✅ Category filter by NUMBER (key)
        if (request()->filled('category')) {
            $selectedKey = (int) request('category');         // 1..5
            $selectedLabel = $category[$selectedKey] ?? null; // Khmer label

            if ($selectedLabel) {
                // Most APIs return label text in $item['category'], so compare by label
                $collection = $collection->filter(
                    fn($item) => ($item['category'] ?? '') === $selectedLabel
                );
            }
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
            'apiTotals',
            'category'
        ));
    }

    public function export(Request $request, $id)
    {
        $type = $this->CashTransactionType()::INCOME;

        // ✅ export is POST => use input()
        $params = array_filter([
            'type'      => $type,
            'from_date' => $request->input('from_date'),
            'to_date'   => $request->input('to_date'),
            'category'  => $request->input('category'), // number
        ], fn($v) => $v !== null && $v !== '');

        $res = $this->api()
            ->withHeaders(['Location-Id' => $id])
            ->download('v1/cash-transaction-exports', $params, null, [
                'Accept' => 'application/octet-stream',
            ]);

        if (!($res instanceof HttpResponse)) {
            return back()->with('error', 'Export failed: no response from API.');
        }

        if (!$res->successful()) {
            return back()->with('error', 'Export failed: API status ' . $res->status());
        }

        $contentType = $res->header('Content-Type') ?? 'application/octet-stream';

        $ext = match (true) {
            Str::contains($contentType, 'csv') => 'csv',
            Str::contains($contentType, 'pdf') => 'pdf',
            Str::contains($contentType, 'excel') || Str::contains($contentType, 'spreadsheet') => 'xlsx',
            default => 'xlsx',
        };

        $filename = 'user_income_export_' . $id . '_' . now()->format('Ymd_His') . '.' . $ext;

        return response()->streamDownload(function () use ($res) {
            echo $res->body();
        }, $filename, ['Content-Type' => $contentType]);
    }

    public function edit($id, $txId)
    {
        $res = $this->api()
            ->withHeaders(['Location-Id' => $id])
            ->get("v1/cash-transactions/{$txId}");

        $income = $res['data'] ?? null;

        if (!$income) {
            return redirect()
                ->route('user_income.list', $id)
                ->with('error', 'Transaction not found.');
        }

        $category = $this->CashTransactionCategory()->getCategories();

        // ✅ convert current category to key for select
        $rawCategory = $income['category'] ?? null;
        $selectedCategoryKey = null;

        if ($rawCategory !== null && $rawCategory !== '') {
            if (is_numeric($rawCategory)) {
                $selectedCategoryKey = (string) (int) $rawCategory;
            } else {
                // if API returned label, map label -> key
                $foundKey = array_search($rawCategory, $category, true);
                $selectedCategoryKey = $foundKey !== false ? (string) $foundKey : null;
            }
        }

        return view('app.user_income.edit', compact(
            'income',
            'id',
            'txId',
            'category',
            'selectedCategoryKey'
        ));
    }

    public function update(Request $request, $id, $txId)
    {
        $categoryMap = $this->CashTransactionCategory()->getCategories();

        $request->validate([
            'transaction_date' => ['required', 'date'],
            'category'         => ['required', 'integer', 'in:' . implode(',', array_keys($categoryMap))],
            'amount'           => ['required', 'numeric'],
            'description'      => ['nullable', 'string'],
        ]);

        $payload = [
            'transaction_date' => $request->input('transaction_date'),
            'category'         => (int) $request->input('category'), // ✅ NUMBER
            'amount'           => (float) $request->input('amount'),
            'description'      => $request->input('description'),
            'type'             => $this->CashTransactionType()::INCOME,
        ];

        $res = $this->api()
            ->withHeaders(['Location-Id' => $id])
            ->patch("v1/cash-transactions/{$txId}", $payload);

        if (is_array($res) && ($res['error'] ?? false)) {
            $apiErrors = $res['errors'] ?? null;

            if (is_array($apiErrors)) {
                // pass API validation errors into Laravel errors
                foreach ($apiErrors as $field => $messages) {
                    if (is_array($messages) && isset($messages[0])) {
                        return back()->withInput()->withErrors([$field => $messages[0]]);
                    }
                }
            }

            return back()->withInput()->with('error', $res['message'] ?? 'Update failed');
        }

        return redirect()
            ->route('user_income.list', $id)
            ->with('success', 'Updated successfully.');
    }

    public function destroy($id, $txId)
    {
        $res = $this->api()
            ->withHeaders(['Location-Id' => $id])
            ->delete("v1/cash-transactions/{$txId}");

        if (is_array($res) && ($res['error'] ?? false)) {
            return back()->with('error', $res['message'] ?? 'Delete failed');
        }

        return redirect()
            ->route('user_income.list', $id)
            ->with('success', 'Deleted successfully.');
    }
}
