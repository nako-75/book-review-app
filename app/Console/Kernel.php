<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * アプリケーションのコマンドスケジュールを定義する。
     */
    protected function schedule(Schedule $schedule): void
    {
        // 毎日20時（日本時間）に読書計画のリマインダーを送信する
        $schedule->command('reading-plan:send-reminders')
            ->dailyAt('20:00')
            ->timezone('Asia/Tokyo');
    }

    /**
     * アプリケーションのコマンドを登録する。
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
