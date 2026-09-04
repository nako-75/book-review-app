<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Genre;
use App\Models\Book;
use Database\Seeders\UserSeeder;
use Database\Seeders\GenreSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            'title'          => 'テスト新規書籍タイトル',
            'author'         => 'テスト著者名',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'description'    => '任意の詳細説明文です。',
            'image_url'      => 'https://example.com/image.jpg',
            'genres'         => [$genre->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(); 

        $this->assertDatabaseHas('books', [
            'title'   => 'テスト新規書籍タイトル',
            'author'  => 'テスト著者名',
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
            'title'          => '編集前のタイトル',
            'author'         => '編集前の著者',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id'        => $user->id,
        ]);

        $response = $this->actingAs($user)->put("/books/{$book->id}", [
            'title'          => '編集後のタイトル',
            'author'         => '編集後の著者',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'genres'         => [$genre->id],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('books', [
            'id'    => $book->id,
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
            'title'          => '削除テスト用タイトル',
            'author'         => '削除テスト用著者',
            'isbn'           => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id'        => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/books/{$book->id}");
        $response->assertRedirect();

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }


}