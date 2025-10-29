<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        $type = $this->CashTransactionType()::INCOME;
        // Get income detail by location
        $incomes = $this->api()->withHeaders(['location_id' => $id])->get("v1/cash-transactions", ['type' => $type]) ?? null;

        return view('app.income.list', compact('incomes'));
    }
}
