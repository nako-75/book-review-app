<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Database\Seeders\BookSeeder;
use Database\Seeders\FavoriteSeeder;
use Database\Seeders\GenreSeeder;
use Database\Seeders\ReviewLikeSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenAccessTest extends TestCase
{
    use RefreshDatabase;

    // 1. 未ログインOKのページ

    public function test_top_page_can_be_accessed(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_books_index_can_be_accessed(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);
    }

    public function test_ranking_can_be_accessed(): void
    {
        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }

    public function test_book_detail_can_be_accessed(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(GenreSeeder::class);
        $this->seed(BookSeeder::class);

        $book = Book::first();

        $response = $this->get("/books/{$book->id}");
        $response->assertStatus(200);
    }

    public function test_login_page_can_be_accessed(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_register_page_can_be_accessed(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    // 2. ログイン必須ページ（未ログインならリダイレクト）

    public function test_books_create_redirects_if_not_logged_in(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }

    public function test_favorites_redirects_if_not_logged_in(): void
    {
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }

    public function test_genres_index_redirects_if_not_logged_in(): void
    {
        $response = $this->get('/genres');
        $response->assertRedirect('/login');
    }

    // 3. ログイン済みユーザーならアクセス可能

    public function test_authenticated_user_can_access_protected_pages(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);
        $this->seed(GenreSeeder::class);
        $this->seed(FavoriteSeeder::class);
        $this->seed(ReviewLikeSeeder::class);

        $book = Book::with('user')->first();

        $user = $book->user ?? User::first();
        $this->actingAs($user);

        $review = Review::where('user_id', $user->id)->first() ?? Review::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'comment' => 'テストレビュー',
            'rating' => 5,
        ]);

        $genre = Genre::first();

        $this->get('/books/create')->assertStatus(200);
        $this->get("/books/{$book->id}/edit")->assertStatus(200);
        $this->get('/favorites')->assertStatus(200);
        $this->get("/reviews/{$review->id}/edit")->assertStatus(200);
        $this->get('/genres')->assertStatus(200);
        $this->get('/genres/create')->assertStatus(200);
        $this->get("/genres/{$genre->id}")->assertStatus(200);
        $this->get("/genres/{$genre->id}/edit")->assertStatus(200);
    }
}