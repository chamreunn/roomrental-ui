<?php

namespace App\Enum;

class AbilitiesStatus
{
    const ADMIN = 'admin';
    const MANAGER = 'manager';
    const USER = 'user';

    /**
     * Get formatted status info.
     *
     * @param string $status
     * @return array
     */
    public static function getStatus(string $status): array
    {
        switch ($status) {
            case self::ADMIN:
                return [
                    'name' => 'admin',
                    'class' => 'badge bg-primary text-primary-fg',
                    'bg' => 'bg-primary',
                    'text' => 'text-primary',
                ];

            case self::MANAGER:
                return [
                    'name' => 'manager',
                    'class' => 'badge bg-warning text-warning-fg',
                    'bg' => 'bg-warning',
                    'text' => 'text-warning',
                ];

            case self::USER:
                return [
                    'name' => 'user',
                    'class' => 'badge bg-success text-success-fg',
                    'bg' => 'bg-success',
                    'text' => 'text-success',
                ];

            default:
                return [
                    'name' => 'unknown',
                    'class' => 'badge bg-secondary-lt',
                    'bg' => 'bg-secondary-lt',
                    'text' => 'text-secondary',
                ];
        }
    }
}
