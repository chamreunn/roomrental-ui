<?php

namespace App\Enum;

class Active
{
    const INACTIVE = 0;
    const ACTIVE = 1;

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
            default:
                return ['label' => 'Unknown', 'class' => 'badge bg-secondary-lt']; // Gray
        }
    }

    public static function all(): array
    {
        return [
            self::INACTIVE => self::getStatus(self::INACTIVE),
            self::ACTIVE => self::getStatus(self::ACTIVE),
        ];
    }
}
