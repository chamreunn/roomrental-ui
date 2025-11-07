<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashTransactionController extends Controller
{
    public function chooseLocation()
    {
        // Get location detail
        $locationResponse = $this->api()->get("v1/locations");
        $locations = $locationResponse['locations']['data'] ?? null;

        return view('app.cash_transaction.choose_location', compact('locations'));
    }

    public function create($location_id)
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('cash_transaction.choose_location', $location_id),
            ],
        ];

        $type = $this->CashTransactionType()->getTypes();
        $category = $this->CashTransactionCategory()->getCategories();

        // Get temporary transactions from session
        $transactions = session('cash_transactions', []);

        return view('app.cash_transaction.create', compact('location_id', 'type', 'category', 'buttons', 'transactions'));
    }

    public function addTemporary(Request $request, $location_id)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'type' => ['required', 'string'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        // IDs
        $typeId = (int) $validated['type'];
        $categoryId = (int) $validated['category'];

        // Names
        $typeName = $this->CashTransactionType()->getTypes()[$typeId] ?? $validated['type'];
        $categoryName = $this->CashTransactionCategory()->getCategories()[$categoryId] ?? $validated['category'];

        // Store both
        $transactions = session('cash_transactions', []);
        $transactions[] = [
            'date' => $validated['date'],
            'type_id' => $typeId,
            'type' => $typeName,
            'category_id' => $categoryId,
            'category' => $categoryName,
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
        ];

        session(['cash_transactions' => $transactions]);

        return redirect()
            ->route('cash_transaction.create', $location_id)
            ->with('success', __('cash_transaction.added_to_list'));
    }

    public function store(Request $request, $location_id)
    {
        $transactions = session('cash_transactions', []);

        if (empty($transactions)) {
            return back()->withErrors(['error' => __('cash_transaction.no_transactions_to_save')]);
        }

        $successCount = 0;
        $failedCount = 0;

        foreach ($transactions as $transaction) {
            // Send only the fields expected by the API
            $payload = [
                'date' => $transaction['date'],
                'type' => $transaction['type_id'],
                'category' => $transaction['category_id'],
                'amount' => $transaction['amount'],
                'description' => $transaction['description'] ?? null,
            ];

            // dd($payload);

            try {
                $response = $this->api()
                    ->withHeaders(['location_id' => $location_id])
                    ->post("v1/cash-transactions", $payload);

                if (!empty($response['success']) && $response['success'] === true) {
                    $successCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Throwable $e) {
                report($e);
                $failedCount++;
            }
        }

        session()->forget('cash_transactions');

        if ($failedCount === 0) {
            return redirect()->route('cash_transaction.create', $location_id)
                ->with('success', __('cash_transaction.saved_successfully'));
        }

        return redirect()->route('cash_transaction.create', $location_id)
            ->with('warning', __("{$successCount} saved, {$failedCount} failed."));
    }

    public function removeTemporary($location_id, $index)
    {
        $transactions = session('cash_transactions', []);

        if (isset($transactions[$index])) {
            unset($transactions[$index]);
            session(['cash_transactions' => array_values($transactions)]); // reindex
        }

        return redirect()
            ->route('cash_transaction.create', $location_id)
            ->with('success', __('cash_transaction.removed_from_list'));
    }
}
