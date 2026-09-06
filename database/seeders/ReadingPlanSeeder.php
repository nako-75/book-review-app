<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;

class ReadingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => '山田 太郎',
                'password' => bcrypt('password'),
            ]
        );

        // 認可テスト用の別ユーザー
        $otherUser = User::factory()->create([
            'name' => '別ユーザー',
            'email' => 'other@example.com',
        ]);

        $books = Book::all();
        if ($books->isEmpty()) {
            return;
        }

        // 1. 期限切れ（過去の日付で未読）
        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->random()->id,
            'target_date' => Carbon::today()->subDays(5),
            'status' => ReadingPlanStatus::Unread,
            'completed_at' => null,
        ]);

        // 2. 期限が今日（進行中）
        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->random()->id,
            'target_date' => Carbon::today(),
            'status' => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ]);

        // 3. 未来の期限（進行中）
        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->random()->id,
            'target_date' => Carbon::today()->addDays(14),
            'status' => ReadingPlanStatus::Reading,
            'completed_at' => null,
        ]);

        // 4. 読了済み（完了・完了日あり）
        ReadingPlan::create([
            'user_id' => $mainUser->id,
            'book_id' => $books->random()->id,
            'target_date' => Carbon::today()->subDays(10),
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => Carbon::today()->subDays(2),
        ]);

        // --- 認可確認用（別ユーザーのデータ） ---
        ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $books->random()->id,
            'target_date' => Carbon::today()->addDays(7),
            'status' => ReadingPlanStatus::Unread,
            'completed_at' => null,
        ]);
    }
}
