<?php

namespace App\Enums;

enum ReadingPlanStatus: int
{
    case Reading = 1;
    case Completed = 2;
    case Expired = 3;

    public function label(): string
    {
        return match($this) {
            self::Reading => '進行中',
            self::Completed => '完了',
            self::Expired => '期限切れ',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Reading => 'bg-blue-100 text-blue-800',
            self::Completed => 'bg-green-100 text-green-800',
            self::Expired => 'bg-red-100 text-red-800',
        };
    }
}