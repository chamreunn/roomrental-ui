<?php

namespace App\Enum;

enum RoomStatus
{
    const Available = 0;
    const Unavailable = 1;
    const Reserved = 2;
    const Maintenance = 3;

    public static function getStatus($status)
    {
        switch ($status) {
            case self::Available:
                return [
                    'name' => 'ទំនេរ',
                    'class' => 'bg-success text-success-fg',
                    'badge' => 'badge bg-success text-success-fg',
                    'text' => 'text-success',
                    'icon' => 'room'
                ];
            case self::Unavailable:
                return [
                    'name' => 'ជាប់រវល់',
                    'class' => 'bg-danger text-danger-fg',
                    'badge' => 'badge bg-danger text-danger-fg',
                    'text' => 'text-danger',
                    'icon' => 'unavailable'
                ];
            case self::Reserved:
                return [
                    'name' => 'បានកក់',
                    'class' => 'bg-yellow text-yellow-fg',
                    'badge' => 'badge bg-yellow text-yellow-fg',
                    'text' => 'text-yellow',
                    'icon' => 'reserved'
                ];
            case self::Maintenance:
                return [
                    'name' => 'កំពុងជួលជុល',
                    'class' => 'bg-secondary text-secondary-fg',
                    'badge' => 'badge bg-secondary text-secondary-fg',
                    'text' => 'text-secondary',
                    'icon' => 'tool'
                ];
            default:
                return ['name' => 'Unknown', 'class' => 'badge bg-secondary-lt', 'text' => 'text-muted'];
        }
    }

    // ✅ Add this method to return all statuses
    public static function all(): array
    {
        return [
            self::Available => self::getStatus(self::Available),
            self::Unavailable => self::getStatus(self::Unavailable),
            self::Reserved => self::getStatus(self::Reserved),
            self::Maintenance => self::getStatus(self::Maintenance),
        ];
    }
}
