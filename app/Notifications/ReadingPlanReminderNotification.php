<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    protected ReadingPlan $readingPlan;

    protected string $timingType;

    /**
     * コンストラクタ
     *
     * @param  string  $timingType  'three_days_before' | 'on_due_date' | 'three_days_after'
     */
    public function __construct(ReadingPlan $readingPlan, string $timingType = 'on_due_date')
    {
        $this->readingPlan = $readingPlan;
        $this->timingType = $timingType;
    }

    /**
     * 通知の送信チャネルを取得する
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * 通知の配列表現を取得する
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $bookTitle = $this->readingPlan->book->title ?? '書籍';

        return match ($this->timingType) {
            'three_days_before' => [
                'reading_plan_id' => $this->readingPlan->id,
                'title' => '読書計画の期日が近づいています',
                'body' => "「{$bookTitle}」の期日まであと3日です。計画通りに進めましょう！",
                'timing' => 'three_days_before',
            ],
            'three_days_after' => [
                'reading_plan_id' => $this->readingPlan->id,
                'title' => '読書計画の期限が過ぎています',
                'body' => "「{$bookTitle}」の期日を過ぎています。読書状況を確認してください。",
                'timing' => 'three_days_after',
            ],
            default => [
                'reading_plan_id' => $this->readingPlan->id,
                'title' => '読書計画の期日です',
                'body' => "「{$bookTitle}」の読書計画の期限は本日です！",
                'timing' => 'on_due_date',
            ],
        };
    }
}
