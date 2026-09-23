<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    // AP01: 書籍一覧を取得できること（検索・ジャンル絞り込み・ページネーション・平均評価・レビュー数）
    public function test_can_get_books_list(): void
    {
        // 既存のシーダーでテストデータをデータベースに投入
        $this->seed(DatabaseSeeder::class);

        // APIにリクエストを送る
        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id', 'title', 'author', 'isbn', 'price', 'detail',
                            'genres', 'reviews_avg_rating', 'reviews_count',
                            'created_at', 'updated_at'
                        ]
                    ],
                    'links',
                    'meta'
                ]);
    }

    // AP02: 書籍詳細を取得できること（ジャンル、レビュー詳細、投稿者名が含まれる）
    public function test_can_get_book_detail(): void
    {
        $this->seed(DatabaseSeeder::class);

        $book = Book::first();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $book->id,
                    'title' => $book->title,
                ]);
    }

    // AP02の例外: 存在しないIDを指定した場合はエラー（404）になること
    public function test_returns_404_for_non_existent_book(): void
    {
        $response = $this->getJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }


    // --- Sanctum 認証セキュリティテスト（未認証時のブロック確認） ---


    // 未認証ユーザーは書籍を新規登録できない
    public function test_unauthenticated_user_cannot_create_book(): void
    {
        $response = $this->postJson('/api/v1/books', [
            'title' => '未認証テスト書籍',
        ]);

        $response->assertStatus(401);
    }

    // 未認証ユーザーは書籍を更新できない
    public function test_unauthenticated_user_cannot_update_book(): void
    {
        $this->seed(DatabaseSeeder::class);
        $book = Book::first();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '改ざんテスト',
        ]);

        $response->assertStatus(401);
    }

    // 未認証ユーザーは書籍を削除できない
    public function test_unauthenticated_user_cannot_delete_book(): void
    {
        $this->seed(DatabaseSeeder::class);
        $book = Book::first();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(401);
    }


    // --- Sanctum 認証済みユーザーの操作テスト ---


    // AP03: Sanctum認証済みユーザーは書籍を新規登録できる
    public function test_can_create_book_with_sanctum_auth(): void
    {
        $this->seed(DatabaseSeeder::class);
        $genre = Genre::first();
        $user = User::first();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            'user_id' => $user ? $user->id : 1,
            'title' => 'APIテスト書籍',
            'author' => 'テスト作者',
            'isbn' => '9784999999999',
            'published_date' => '2026-08-30',
            'description' => 'API経由での登録テストです。',
            'genres' => $genre ? [$genre->id] : [],
        ]);

        $response->assertStatus(201)
                ->assertJsonFragment(['title' => 'APIテスト書籍']);

        $this->assertDatabaseHas('books', ['title' => 'APIテスト書籍']);
    }

    // AP03のバリデーション: 認証済みでも必須項目が空のときはエラーになる
    public function test_book_creation_requires_fields(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::first();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'author', 'isbn', 'user_id']);
    }

    // AP04: Sanctum認証済みユーザーは書籍を更新できる
    public function test_can_update_book_with_sanctum_auth(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::first();
        $book = Book::first();
        $book->user_id = $user->id;
        $book->save();

        $updateData = [
            'title' => '新しいタイトルに更新',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'description' => $book->description,
        ];

        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/books/{$book->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonFragment(['title' => '新しいタイトルに更新']);

        $this->assertDatabaseHas('books', ['title' => '新しいタイトルに更新']);
    }

    // AP05: Sanctum認証済みユーザーは書籍を削除できる
    public function test_can_delete_book_with_sanctum_auth(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::first();
        $book = Book::first();
        $book->user_id = $user->id;
        $book->save();

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}