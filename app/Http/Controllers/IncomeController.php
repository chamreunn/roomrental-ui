<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Str;

class IncomeController extends Controller
{
    public function index()
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.income.index', compact('locations'));
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

        // ✅ Fetch from API
        $response = $this->api()
            ->withHeaders(['Location-Id' => $id])
            ->get('v1/cash-transactions', [
                'type' => $type,
                'page' => $page,
            ]);

        $data        = $response['data']['data'] ?? [];
        $perPage     = $response['data']['per_page'] ?? 10;
        $currentPage = $response['data']['current_page'] ?? $page;

        $apiTotals = collect($response['totals'] ?? []);
        $collection = collect($data);

        // ===============================
        // LOCAL FILTERING
        // ===============================
        if (request()->filled('from_date')) {
            $collection = $collection->filter(
                fn($item) => ($item['transaction_date'] ?? '') >= request('from_date')
            );
        }

        if (request()->filled('to_date')) {
            $collection = $collection->filter(
                fn($item) => ($item['transaction_date'] ?? '') <= request('to_date')
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
        // TOTAL AFTER FILTER
        // ===============================
        $totalIncome = $collection->sum(fn($item) => (float) ($item['amount'] ?? 0));

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

        return view('app.income.list', compact(
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

        // ✅ Use input() because export is POST
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

        $filename = 'income_export_' . $id . '_' . now()->format('Ymd_His') . '.' . $ext;

        return response()->streamDownload(function () use ($res) {
            echo $res->body();
        }, $filename, ['Content-Type' => $contentType]);
    }

    public function edit($locationId, $txId)
    {
        $res = $this->api()
            ->withHeaders(['Location-Id' => $locationId])
            ->get("v1/cash-transactions/{$txId}");

        $income = $res['data'] ?? null;

        if (!$income) {
            return redirect()
                ->route('income.list', $locationId)
                ->with('error', 'Transaction not found.');
        }

        // categories: [1=>"ថ្លៃជួលបន្ទប់", 2=>"ថ្លៃអគ្គិសនី", ...]
        $category = $this->CashTransactionCategory()->getCategories();

        // ✅ Convert stored category to key (number) for dropdown
        $rawCategory = $income['category'] ?? null;

        $selectedCategoryKey = null;

        if ($rawCategory !== null && $rawCategory !== '') {
            if (is_numeric($rawCategory)) {
                $selectedCategoryKey = (string) (int) $rawCategory;
            } else {
                // If API returned label, map label -> key
                $foundKey = array_search($rawCategory, $category, true);
                $selectedCategoryKey = $foundKey !== false ? (string) $foundKey : null;
            }
        }

        return view('app.income.edit', compact(
            'income',
            'locationId',
            'txId',
            'category',
            'selectedCategoryKey'
        ));
    }

    public function update(Request $request, $locationId, $txId)
    {
        $categoryMap = $this->CashTransactionCategory()->getCategories(); // [1=>"...", 2=>"..."]

        $request->validate([
            'transaction_date' => ['required', 'date'],
            'category'         => ['required', 'integer', 'in:' . implode(',', array_keys($categoryMap))],
            'amount'           => ['required', 'numeric'],
            'description'      => ['nullable', 'string'],
        ]);

        $payload = [
            'transaction_date' => $request->input('transaction_date'),
            'category'         => (int) $request->input('category'), // ✅ send NUMBER
            'amount'           => (float) $request->input('amount'),
            'description'      => $request->input('description'),
            'type'             => $this->CashTransactionType()::INCOME,
        ];

        $res = $this->api()
            ->withHeaders(['Location-Id' => $locationId])
            ->patch("v1/cash-transactions/{$txId}", $payload);

        if (is_array($res) && ($res['error'] ?? false)) {
            // If API returns validation errors, show them too
            $apiErrors = $res['errors'] ?? null;

            if (is_array($apiErrors) && isset($apiErrors['category'][0])) {
                return back()->withInput()->withErrors(['category' => $apiErrors['category'][0]]);
            }

            return back()->withInput()->with('error', $res['message'] ?? 'Update failed');
        }

        return redirect()
            ->route('income.list', $locationId)
            ->with('success', 'Updated successfully.');
    }

    public function destroy($locationId, $txId)
    {
        $res = $this->api()
            ->withHeaders(['Location-Id' => $locationId])
            ->delete("v1/cash-transactions/{$txId}");

        if (is_array($res) && ($res['error'] ?? false)) {
            return back()->with('error', $res['message'] ?? 'Delete failed');
        }

        return redirect()
            ->route('income.list', $locationId)
            ->with('success', 'Deleted successfully.');
    }
}
