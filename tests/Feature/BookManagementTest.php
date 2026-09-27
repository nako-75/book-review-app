<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\GenreSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BookManagementTest extends TestCase
{
    use RefreshDatabase;

    // 1. 書籍の新規登録
    public function test_authenticated_user_can_create_a_book(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(GenreSeeder::class);

        $user = User::first();
        $genre = Genre::first();

        $response = $this->actingAs($user)->post('/books', [
            'title' => 'テスト新規書籍タイトル',
            'author' => 'テスト著者名',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'description' => '任意の詳細説明文です。',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'title' => 'テスト新規書籍タイトル',
            'author' => 'テスト著者名',
            'user_id' => $user->id,
        ]);
    }

    // 2. 書籍の更新
    public function test_authenticated_user_can_update_a_book(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(GenreSeeder::class);

        $user = User::first();
        $genre = Genre::first();

        $book = Book::create([
            'title' => '編集前のタイトル',
            'author' => '編集前の著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put("/books/{$book->id}", [
            'title' => '編集後のタイトル',
            'author' => '編集後の著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '編集後のタイトル',
            'author' => '編集後の著者',
        ]);
    }

    // 3. 書籍の削除
    public function test_authenticated_user_can_delete_a_book(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $book = Book::create([
            'title' => '削除テスト用タイトル',
            'author' => '削除テスト用著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    // ここから応用機能テスト

    // キーワード検索のテスト
    public function test_can_search_books_by_keyword()
    {
        $user = User::factory()->create();

        $book1 = Book::create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2023-01-01',
            'description' => 'テスト説明',
        ]);

        $book2 = Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel実践',
            'author' => 'テスト著者',
            'isbn' => '9784000000002',
            'published_date' => '2023-01-02',
            'description' => 'テスト説明',
        ]);

        $response = $this->actingAs($user)->get('/books?keyword=PHP');

        $response->assertStatus(200);
        $response->assertSee('PHP入門');
        $response->assertDontSee('Laravel実践');
    }

    // ジャンルフィルタのテスト
    public function test_can_filter_books_by_genre()
    {
        $user = User::factory()->create();

        $genre1 = Genre::create(['name' => 'PHP']);
        $genre2 = Genre::create(['name' => 'Laravel']);

        $book1 = Book::create([
            'user_id' => $user->id,
            'title' => 'PHP入門',
            'author' => 'テスト著者',
            'isbn' => '9784000000001',
            'published_date' => '2023-01-01',
            'description' => 'テスト説明',
        ]);
        $book1->genres()->attach($genre1->id);

        $book2 = Book::create([
            'user_id' => $user->id,
            'title' => 'Laravel実践',
            'author' => 'テスト著者',
            'isbn' => '9784000000002',
            'published_date' => '2023-01-02',
            'description' => 'テスト説明',
        ]);
        $book2->genres()->attach($genre2->id);

        $response = $this->actingAs($user)->get('/books?genre='.$genre2->id);

        $response->assertStatus(200);
        $response->assertSee('Laravel実践');
        $response->assertDontSee('PHP入門');
    }

    public function test_can_sort_books_by_newest_and_oldest()
    {
        $user = User::factory()->create();

        $bookOld = Book::create([
            'user_id' => $user->id,
            'title' => 'B Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000003',
            'published_date' => '2023-01-01',
            'created_at' => now()->subDays(5),
        ]);

        $bookNew = Book::create([
            'user_id' => $user->id,
            'title' => 'C Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000004',
            'published_date' => '2023-01-01',
            'created_at' => now()->subDay(),
        ]);

        // デフォルト（新しい順）のテスト
        $response = $this->actingAs($user)->get('/books');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['C Book', 'B Book']);

        // 古い順のテスト
        $response = $this->actingAs($user)->get('/books?sort=oldest');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['B Book', 'C Book']);
    }

    // タイトル順のテスト
    public function test_can_sort_books_by_title()
    {
        $user = User::factory()->create();

        Book::create([
            'user_id' => $user->id,
            'title' => 'B Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000003',
            'published_date' => '2023-01-01',
        ]);
        Book::create([
            'user_id' => $user->id,
            'title' => 'A Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000005',
            'published_date' => '2023-01-01',
        ]);
        Book::create([
            'user_id' => $user->id,
            'title' => 'C Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000004',
            'published_date' => '2023-01-01',
        ]);

        $response = $this->actingAs($user)->get('/books?sort=title');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['A Book', 'B Book', 'C Book']);
    }

    //　評価が高い順のテスト
    public function test_can_sort_books_by_rating()
    {
        $user = User::factory()->create();

        $lowRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => 'Low Rated Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000006',
            'published_date' => '2023-01-01',
        ]);

        $highRatedBook = Book::create([
            'user_id' => $user->id,
            'title' => 'High Rated Book',
            'author' => 'テスト著者',
            'isbn' => '9784000000007',
            'published_date' => '2023-01-01',
        ]);

        Review::create([
            'book_id' => $lowRatedBook->id,
            'user_id' => $user->id,
            'rating' => 1,
            'comment' => 'イマイチでした',
        ]);

        Review::create([
            'book_id' => $highRatedBook->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => '最高でした',
        ]);

        $response = $this->actingAs($user)->get('/books?sort=rating');
        $response->assertStatus(200);
        $response->assertSeeInOrder(['High Rated Book', 'Low Rated Book']);
    }

    // 別ユーザーの書籍更新（認可のテスト）
    public function test_user_cannot_update_other_users_book(): void
    {
        $this->seed(GenreSeeder::class);
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $genre = Genre::first();

        $book = Book::create([
            'title' => 'ユーザーAの本',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $userA->id,
        ]);

        $response = $this->actingAs($userB)->put("/books/{$book->id}", [
            'title' => '改ざんタイトル',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'ユーザーAの本',
        ]);
    }

    // 別ユーザーの書籍削除（認可のテスト）
    public function test_user_cannot_delete_other_users_book(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $book = Book::create([
            'title' => 'ユーザーAの本',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $userA->id,
        ]);

        $response = $this->actingAs($userB)->delete("/books/{$book->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }

    // 未ログインの書籍登録（認可のテスト）
    public function test_guest_cannot_create_a_book(): void
    {
        $this->seed(GenreSeeder::class);
        $genre = Genre::first();

        $response = $this->post('/books', [
            'title' => 'ゲストの投稿',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertRedirect(route('login'));
    }

    // 未ログインの書籍更新（認可のテスト）
    public function test_guest_cannot_update_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'title' => '元のタイトル',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $response = $this->put("/books/{$book->id}", [
            'title' => '改ざんタイトル',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
        ]);

        $response->assertRedirect(route('login'));
    }

    // 未ログインの書籍削除（認可のテスト）
    public function test_guest_cannot_delete_a_book(): void
    {
        $user = User::factory()->create();
        $book = Book::create([
            'title' => '削除対象の本',
            'author' => '著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $response = $this->delete("/books/{$book->id}");

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    // ISBNで書籍を検索できること
    public function test_can_fetch_book_info_by_isbn(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト自動取得タイトル',
                            'authors' => ['テスト著者名'],
                            'publishedDate' => '2026-01-01',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get('/books/isbn/9784163907154');

        $response->assertStatus(200);

        $response->assertJson([
            'title' => 'テスト自動取得タイトル',
            'author' => 'テスト著者名',
            'published_date' => '2026-01-01',
        ]);
    }
}
