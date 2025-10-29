<?php

namespace App\Enum;

class CashTransactionCategory
{
    const ROOM_FEE = 1;
    const ELECTRICITY = 2;
    const WATER = 3;
    const SALARY = 4;
    const OTHER = 5;

    public static function getCategories()
    {
        return [
            self::ROOM_FEE => __('cash_transaction.room_fee'),
            self::ELECTRICITY => __('cash_transaction.electricity'),
            self::WATER => __('cash_transaction.water'),
            self::SALARY => __('cash_transaction.salary'),
            self::OTHER => __('cash_transaction.other'),
        ];
    }
}
