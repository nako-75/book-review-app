<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    // ログインユーザーは読書計画一覧を表示できる
    public function test_user_can_view_reading_plans_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
    }

    // ログインユーザーは新しい読書計画を登録できる
    public function test_user_can_create_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'title' => 'テスト用の本',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
        ]);
    }

    // ログインユーザーは読書計画を完了にできる
    public function test_user_can_complete_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'title' => 'テスト用の本',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed,
        ]);
    }

    // ログインユーザーは自分の読書計画を更新できる
    public function test_user_can_update_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'title' => 'テスト用の本',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => '2027-01-01',
        ]);

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => '2027-01-01',
        ]);
    }

    // ログインユーザーは自分の読書計画を削除できる
    public function test_user_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();

        $book = Book::create([
            'title' => 'テスト用の本',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $user->id,
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));

        $this->assertModelMissing($readingPlan);
    }

    // 他人の読書計画は更新できない（認可のテスト）
    public function test_user_cannot_update_others_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::create([
            'title' => 'テスト用の本',
            'author' => 'テスト著者',
            'isbn' => '9784163907154',
            'published_date' => '2026-01-01',
            'user_id' => $owner->id,
        ]);

        $readingPlan = ReadingPlan::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
            'status' => ReadingPlanStatus::Reading,
        ]);

        $response = $this->actingAs($otherUser)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => '2027-01-01',
        ]);

        $response->assertStatus(403);
    }
}
