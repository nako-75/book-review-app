<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Review;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewManagementTest extends TestCase
{
    use RefreshDatabase;

    // 1. レビューの新規投稿
    public function test_authenticated_user_can_create_a_review(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $book = Book::create([
            'title'          => 'レビュー対象の書籍',
            'author'         => 'テスト著者',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id'        => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating'  => 5,
            'comment' => 'とても素晴らしい本でした！',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 5,
            'comment' => 'とても素晴らしい本でした！',
        ]);
    }

    // 2. レビューの更新
    public function test_authenticated_user_can_update_a_review(): void
    {

        $this->seed(UserSeeder::class);
        $user = User::first();

        $book = Book::create([
            'title'          => 'レビュー対象の書籍',
            'author'         => 'テスト著者',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id'        => $user->id,
        ]);

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 3,
            'comment' => '編集前のコメントです。',
        ]);

        $response = $this->actingAs($user)->put("/reviews/{$review->id}", [
            'rating'  => 4,
            'comment' => '編集後のコメントに更新しました！',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id'      => $review->id,
            'rating'  => 4,
            'comment' => '編集後のコメントに更新しました！',
        ]);
    }

    // 3. レビューの削除
    public function test_authenticated_user_can_delete_a_review(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $book = Book::create([
            'title'          => 'レビュー対象の書籍',
            'author'         => 'テスト著者',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id'        => $user->id,
        ]);

        $review = Review::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating'  => 5,
            'comment' => '削除テスト用のコメントです。',
        ]);

        $response = $this->actingAs($user)->delete("/reviews/{$review->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}