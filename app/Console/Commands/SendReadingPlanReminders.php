<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Support\Facades\DB;
use App\Enums\ReadingPlanStatus;

class SendReadingPlanReminders extends Command
{
    protected $signature = 'reading-plan:send-reminders';
    protected $description = '読書計画のリマインダー通知および期限切れステータスの更新を行います';

    public function handle()
    {
        $this->info('読書計画のリマインダー処理を開始します...');
        $today = now()->toDateString();

        // 1. 【当日】が期日の計画に通知を送る（進行中のもの）
        $dueTodayPlans = ReadingPlan::whereDate('target_date', $today)
            ->where('status', ReadingPlanStatus::Reading)
            ->get();

        foreach ($dueTodayPlans as $plan) {
            if ($plan->user) {
                $plan->user->notify(new ReadingPlanReminderNotification($plan, 'on_due_date'));
            }
        }

        // 2. 【期日を過ぎた（3日後以降など、あるいは単純に過ぎた）】計画のステータスを期限切れに変更
        $expiredPlans = ReadingPlan::whereDate('target_date', '<', $today)
            ->where('status', ReadingPlanStatus::Reading)
            ->get();

        foreach ($expiredPlans as $plan) {
            DB::transaction(function () use ($plan) {
                $plan->update([
                    'status' => ReadingPlanStatus::Expired,
                ]);

                if ($plan->user) {
                    $plan->user->notify(new ReadingPlanReminderNotification($plan, 'three_days_after'));
                }
            });
        }

        $this->info('リマインダー処理が完了しました。');
    }
}
