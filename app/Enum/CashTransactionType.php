<?php

namespace App\Enum;

class CashTransactionType
{
    const INCOME = 1;
    const EXPENSE = 2;

    public static function getTypes()
    {
        return [
            self::INCOME => __('cash_transaction.income'),
            self::EXPENSE => __('cash_transaction.expense'),
        ];
    }
}
