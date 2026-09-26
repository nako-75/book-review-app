<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createTestBook(User $user): Book
    {
        return Book::create([
            'title' => 'テスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);
    }

    // リマインダーバッチの実行テスト（通知送信と期限切れステータスの更新）
    public function test_send_reminders_command_updates_status_and_sends_notifications(): void
    {
        Carbon::setTestNow('2026-09-23 10:00:00');
        Notification::fake();

        $user = User::factory()->create();
        $book = $this->createTestBook($user);

        // 当日期日の計画
        $dueTodayPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-23',
            'status' => ReadingPlanStatus::Reading,
        ]);

        // 過去期日の計画（期限切れ対象）
        $expiredPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-20',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $this->artisan('reading-plan:send-reminders')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $expiredPlan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);

        Notification::assertSentTo(
            $user,
            ReadingPlanReminderNotification::class,
            function ($notification) use ($dueTodayPlan, $user) {
                $data = $notification->toArray($user);

                return $data['reading_plan_id'] === $dueTodayPlan->id
                    && $data['timing'] === 'on_due_date';
            }
        );

        Carbon::setTestNow();
    }

    // ユーザーが通知一覧画面を閲覧できる
    public function test_user_can_view_notifications_index(): void
    {
        $user = User::factory()->create();
        $book = $this->createTestBook($user);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-23',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $user->notify(new ReadingPlanReminderNotification($plan, 'on_due_date'));

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('通知一覧');
    }

    // ユーザーが通知を「既読にする」ことができる
    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $book = $this->createTestBook($user);

        $plan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-09-23',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $user->notify(new ReadingPlanReminderNotification($plan, 'on_due_date'));
        $notification = $user->notifications()->first();

        $this->assertNull($notification->read_at);

        $response = $this->actingAs($user)->post(route('notifications.read', $notification->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', '通知を既読にしました。');

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
