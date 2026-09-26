<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    // ログインユーザーは読書レポート一覧（集計画面）を正常に閲覧できる
    public function test_user_can_view_reports_index(): void
    {
        $user = User::factory()->create();

        $genre = Genre::create(['name' => '小説']);
        $book = Book::create([
            'title' => 'テスト小説',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => '最高でした！',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('stats');
        $response->assertSee('テスト小説');
    }

    // 他のユーザーのレビューが自分のレポート集計に影響しない
    public function test_report_stats_only_contains_authenticated_user_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myBook = Book::create([
            'title' => '私の本',
            'author' => '著者A',
            'isbn' => '9784163907151',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);
        Review::create([
            'user_id' => $user->id,
            'book_id' => $myBook->id,
            'rating' => 5,
            'comment' => '最高！',
        ]);

        $otherBook = Book::create([
            'title' => '他人の本',
            'author' => '著者B',
            'isbn' => '9784163907152',
            'published_date' => '2026-01-01',
            'user_id' => $otherUser->id,
        ]);
        Review::create([
            'user_id' => $otherUser->id,
            'book_id' => $otherBook->id,
            'rating' => 1,
            'comment' => 'イマイチ...',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] == 1
                && (float) $stats['summary']['average_rating'] === 5.0;
        });

        $response->assertDontSee('他人の本');
    }

    // 評価分布、高評価書籍TOP5、ジャンル別評価傾向が正しく集計されていること
    public function test_report_detailed_statistics_are_calculated_correctly(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::create(['name' => '技術書']);
        $genre2 = Genre::create(['name' => '小説']);

        $book1 = Book::create([
            'title' => 'Laravel入門',
            'author' => '著者A',
            'isbn' => '9784163907153',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $book2 = Book::create([
            'title' => 'すごい小説',
            'author' => '著者B',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        // 中間テーブル経由でジャンルを紐付ける
        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        // 評価5（book1）と評価3（book2）を作成
        Review::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
            'comment' => '最高！',
        ]);

        Review::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
            'comment' => '普通',
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertViewHas('stats', function ($stats) {
            // 評価分布の検証（星5が1件、星3が1件）
            $distribution = $stats['rating_distribution'];

            // 高評価書籍 TOP5 の検証（評価4以上なので 'Laravel入門' が含まれ、星3の 'すごい小説' は含まれない）
            $topBooks = $stats['top_rated_books'];
            $hasTopBook = collect($topBooks)->contains('title', 'Laravel入門');
            $doesNotHaveLowBook = ! collect($topBooks)->contains('title', 'すごい小説');

            // ジャンル別評価傾向の検証
            $genreRatings = $stats['genre_ratings'];

            return $distribution[4] === 1
                && $distribution[2] === 1
                && $hasTopBook
                && $doesNotHaveLowBook
                && count($genreRatings) > 0;
        });
    }
}
