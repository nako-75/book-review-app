<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // 1. 新規登録できる
    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    // 2. 作成したユーザーでログインできる
    public function test_users_can_authenticate_using_seeder_data(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    // 3. ログアウト
    public function test_users_can_logout(): void
    {
        $this->seed(UserSeeder::class);
        $user = User::first();
        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // 4. 未ログインユーザーが認証必須ページにアクセスするとリダイレクトされること
    public function test_guest_cannot_access_protected_actions(): void
    {
        $response = $this->post('/reviews/1/like');
        $response->assertRedirect('/login');
    }

    // 5. 必須項目が空の場合は登録できないこと（代表的なバリデーションテスト）
    public function test_registration_requires_fields(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }
}