<?php

namespace App\Enum;

class InvoiceStatus
{
    const DRAFT = 0;
    const UNPAID = 1;
    const PAID = 2;
    const CANCELLED = 3;

    /**
     * Get the display info for a status
     *
     * @param int $status
     * @return array
     */
    public static function getStatus(int $status): array
    {
        switch ($status) {
            case self::DRAFT:
                return [
                    'name' => 'invoice.draft',
                    'badge' => 'badge bg-secondary text-secondary-fg', // Gray
                    'text' => 'text-secondary'
                ];
            case self::UNPAID:
                return [
                    'name' => 'invoice.unpaid',
                    'badge' => 'badge bg-warning text-warning-fg', // Yellow
                    'text' => 'text-warning'
                ];
            case self::PAID:
                return [
                    'name' => 'invoice.paid',
                    'badge' => 'badge bg-success text-success-fg', // Green
                    'text' => 'text-success'
                ];
            case self::CANCELLED:
                return [
                    'name' => 'invoice.cancelled',
                    'badge' => 'badge bg-danger text-danger-fg', // Red
                    'text' => 'text-danger'
                ];
            default:
                return [
                    'name' => 'invoice.unknown',
                    'badge' => 'badge bg-secondary text-secondary-fg', // Gray
                    'text' => 'text-secondary'
                ];
        }
    }

    /**
     * Return all statuses with keys
     *
     * @return array
     */
     public static function all(): array
    {
        return [
            self::DRAFT => self::getStatus(self::DRAFT),
            self::UNPAID => self::getStatus(self::UNPAID),
            self::PAID => self::getStatus(self::PAID),
            self::CANCELLED => self::getStatus(self::CANCELLED),
        ];
    }
}
