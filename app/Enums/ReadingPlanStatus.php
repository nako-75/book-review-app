<?php

namespace App\Enums;

enum ReadingPlanStatus: int
{
    case Unread = 1;
    case Reading = 2;
    case Completed = 3;

    public function label(): string
    {
        return match($this) {
            self::Unread => '未読',
            self::Reading => '進行中',
            self::Completed => '完了',
        };
    }
}