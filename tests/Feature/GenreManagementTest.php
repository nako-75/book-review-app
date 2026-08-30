<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Genre;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreManagementTest extends TestCase
{
    use RefreshDatabase;

    // 1. ジャンルの新規作成
    public function test_authenticated_user_can_create_a_genre(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $response = $this->actingAs($user)->post('/genres', [
            'name' => '小説・文学',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('genres', [
            'name' => '小説・文学',
        ]);
    }

    // 2. ジャンルの更新
    public function test_authenticated_user_can_update_a_genre(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $genre = Genre::create([
            'name' => '旧ジャンル名',
        ]);

        $response = $this->actingAs($user)->put("/genres/{$genre->id}", [
            'name' => '新ジャンル名',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('genres', [
            'id'   => $genre->id,
            'name' => '新ジャンル名',
        ]);
    }

    // 3. ジャンルの削除
    public function test_authenticated_user_can_delete_a_genre(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();

        $genre = Genre::create([
            'name' => '削除用ジャンル',
        ]);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect();

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }
}