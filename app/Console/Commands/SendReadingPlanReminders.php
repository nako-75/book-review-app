<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendReadingPlanReminders extends Command
{
    protected $signature = 'reading-plan:send-reminders';

    protected $description = '読書計画のリマインダー通知および期限切れステータスの更新を行います';

    public function handle()
    {
        $this->info('読書計画のリマインダー処理を開始します...');
        $today = now()->toDateString();
        $threeDaysLater = now()->addDays(3)->toDateString();

        // 1. 【3日前】が期日の計画に通知を送る
        $threeDaysBeforePlans = ReadingPlan::whereDate('target_date', $threeDaysLater)
            ->where('status', ReadingPlanStatus::Reading)
            ->get();

        foreach ($threeDaysBeforePlans as $plan) {
            if ($plan->user) {
                $plan->user->notify(new ReadingPlanReminderNotification($plan, 'three_days_before'));
            }
        }

        // ２. 【当日】が期日の計画に通知を送る（進行中のもの）
        $dueTodayPlans = ReadingPlan::whereDate('target_date', $today)
            ->where('status', ReadingPlanStatus::Reading)
            ->get();

        foreach ($dueTodayPlans as $plan) {
            if ($plan->user) {
                $plan->user->notify(new ReadingPlanReminderNotification($plan, 'on_due_date'));
            }
        }

        // ３. 【期日を過ぎた（3日後以降など、あるいは単純に過ぎた）】計画のステータスを期限切れに変更
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
