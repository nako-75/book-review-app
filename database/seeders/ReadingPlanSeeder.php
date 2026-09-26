<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    /**
     * 読書計画機能の動作検証用ダミーデータを生成する
     */
    public function run(): void
    {
        $yamada = User::findOrFail(1);

        // 認可テスト用の別ユーザー
        $otherUser = User::firstOrCreate(
            ['email' => 'other@example.com'],
            [
                'name' => '別ユーザー',
                'password' => bcrypt('password'),
            ]
        );

        $books = Book::take(5)->get();
        if ($books->count() < 3) {
            return;
        }

        // ID: 1 の山田太郎のシナリオ別データ

        // 進行中（期限内）の計画
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[0]->id,
            'status' => ReadingPlanStatus::Reading,
            'target_date' => Carbon::today()->addDays(5),
        ]);

        // 完了済みの計画
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[1]->id,
            'status' => ReadingPlanStatus::Completed,
            'target_date' => Carbon::today()->subDays(3),
        ]);

        // 期限切れ（未達成のまま期限超過）の計画
        ReadingPlan::create([
            'user_id' => $yamada->id,
            'book_id' => $books[2]->id,
            'status' => ReadingPlanStatus::Expired,
            'target_date' => Carbon::today()->subDays(2),
        ]);

        // 未着手かつ十分な余裕がある計画
        if (isset($books[3])) {
            ReadingPlan::create([
                'user_id' => $yamada->id,
                'book_id' => $books[3]->id,
                'status' => ReadingPlanStatus::Reading,
                'target_date' => Carbon::today()->addDays(14),
            ]);
        }

        // 認可確認用（別ユーザーのデータ）

        ReadingPlan::create([
            'user_id' => $otherUser->id,
            'book_id' => $books[0]->id,
            'status' => ReadingPlanStatus::Reading,
            'target_date' => Carbon::today()->addDays(7),
        ]);
    }
}
