<?php

namespace App\Enum;

class Active
{
    const INACTIVE = 0;
    const ACTIVE = 1;
    const PENDING = 2;

    public static function getStatus($status)
    {
        switch ($status) {
            case self::ACTIVE:
                return [
                    'name' => 'active.active',
                    'badge' => 'badge bg-success text-success-fg',
                    'text' => 'text-success'
                ]; // Light Green
            case self::INACTIVE:
                return [
                    'name' => 'active.in_active',
                    'badge' => 'badge bg-secondary text-secondary-fg',
                    'text' => 'text-secondary'
                ]; // Red
            case self::PENDING:
                return [
                    'name' => 'active.pending',
                    'badge' => 'badge bg-warning text-warning-fg',
                    'text' => 'text-warning'
                ]; // Red
            default:
                return ['label' => 'Unknown', 'class' => 'badge bg-secondary-lt']; // Gray
        }
    }
}
