<?php

namespace App\Enum;

enum Status
{
    const ACTIVE = '1';
    const INACTIVE = '0';

    // Define the function correctly
    public static function getStatus($status)
    {
        switch ($status) {
            case self::ACTIVE:
                return [
                    'name' => 'Active',
                    'class' => 'badge bg-success text-success-fg',
                    'text' => 'text-success'
                ]; // Light Green
            case self::INACTIVE:
                return [
                    'name' => 'Inactive', 
                    'class' => 'badge bg-danger text-danger-fg', 
                    'text' => 'text-danger']; // Red
            default:
                return ['label' => 'Unknown', 'class' => 'badge bg-secondary-lt']; // Gray
        }
    }
}
