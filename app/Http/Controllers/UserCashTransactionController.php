<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserCashTransactionController extends Controller
{
    public function create()
    {
        $buttons = [
            [
                'text' => __('titles.back'),
                'icon' => 'chevrons-left',
                'class' => 'btn btn-outline-primary btn-5 d-none d-sm-inline-block',
                'url' => route('dashboard.user'),
            ],
        ];

        $type      = $this->CashTransactionType()->getTypes();
        $category  = $this->CashTransactionCategory()->getCategories();
        $locations = Session::get('user.user_locations', []);

        // 🔥 THIS WAS MISSING
        $transactions = session('cash_transactions', []);

        return view(
            'app.user_cash_transaction.create',
            compact('type', 'category', 'buttons', 'locations', 'transactions')
        );
    }

    public function addTemporary(Request $request)
    {
        $validated = $request->validate([
            'date'        => ['required', 'date'],
            'location_id' => ['required', 'string'],
            'type'        => ['required', 'integer'],
            'category'    => ['required', 'integer'],
            'amount'      => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $typeId     = (int) $validated['type'];
        $categoryId = (int) $validated['category'];
        $locationId = $validated['location_id'];

        $types      = $this->CashTransactionType()->getTypes();
        $categories = $this->CashTransactionCategory()->getCategories();

        /* ---------------------------------------
     |  GET LOCATION NAME FROM SESSION
     |----------------------------------------
     */
        $locations = Session::get('user.user_locations', []);

        $locationName = null;

        foreach ($locations as $loc) {
            if (($loc['location_id'] ?? null) === $locationId) {
                $locationName = $loc['location']['location_name'] ?? null;
                break;
            }
        }

        // fallback safety
        $locationName = $locationName ?? 'Unknown';

        /* ---------------------------------------
     |  STORE TRANSACTION
     |----------------------------------------
     */
        $transactions = session('cash_transactions', []);

        $transactions[] = [
            'date'           => $validated['date'],
            'location_id'    => $locationId,
            'location_name'  => $locationName,

            'type_id'        => $typeId,
            'type'           => $types[$typeId] ?? 'Unknown',

            'category_id'    => $categoryId,
            'category'       => $categories[$categoryId] ?? 'Unknown',

            'amount'         => $validated['amount'],
            'description'    => $validated['description'] ?? null,
        ];

        session(['cash_transactions' => $transactions]);

        return redirect()
            ->route('user_cash_transaction.create')
            ->with('success', __('cash_transaction.added_to_list'));
    }

    public function removeTemporary($location_id, $index)
    {
        $transactions = session('cash_transactions', []);

        if (isset($transactions[$index])) {
            unset($transactions[$index]);
            session(['cash_transactions' => array_values($transactions)]); // reindex
        }

        return redirect()
            ->route('user_cash_transaction.create', $location_id)
            ->with('success', __('cash_transaction.removed_from_list'));
    }

    public function store(Request $request)
    {
        $transactions = session('cash_transactions', []);

        if (empty($transactions)) {
            return back()->withErrors([
                'error' => __('cash_transaction.no_transactions_to_save')
            ]);
        }

        // ✅ Group by location
        $grouped = collect($transactions)->groupBy('location_id');

        $successCount = 0;
        $failedCount  = 0;

        foreach ($grouped as $locationId => $items) {

            foreach ($items as $transaction) {

                $payload = [
                    'date'        => $transaction['date'],
                    'type'        => $transaction['type_id'],
                    'category'    => $transaction['category_id'],
                    'amount'      => $transaction['amount'],
                    'description' => $transaction['description'] ?? null,
                ];

                try {
                    $response = $this->api()->post(
                        'v1/cash-transactions',
                        $payload,
                        token: null,
                        asForm: false,
                        files: [],
                        fileField: 'documents[]',
                        moreHeaders: [
                            'Location-Id' => $locationId, // ✅ CORRECT
                        ]
                    );

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
        }

        // ✅ Clear only after processing all
        session()->forget('cash_transactions');

        if ($failedCount === 0) {
            return redirect()
                ->route('user_cash_transaction.create')
                ->with('success', __('cash_transaction.saved_successfully'));
        }

        return redirect()
            ->route('user_cash_transaction.create')
            ->with('warning', __("{$successCount} saved, {$failedCount} failed."));
    }
}
