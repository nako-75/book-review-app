<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Database\Seeders\UserSeeder;
use Database\Seeders\BookSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    // 1. お気に入り登録
    public function test_authenticated_user_can_favorite_a_book(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);

        $user = User::first();
        $book = Book::first();

        $response = $this->actingAs($user)->post("/books/{$book->id}/favorite");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // 2. お気に入り解除（トグル）
    public function test_authenticated_user_can_unfavorite_a_book(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);

        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->attach($book->id);
        $response = $this->actingAs($user)->post("/books/{$book->id}/favorite");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    // 3. お気に入り一覧表示
    public function test_authenticated_user_can_view_favorites_index(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);

        $user = User::first();
        $book = Book::first();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee($book->title);
    }
}