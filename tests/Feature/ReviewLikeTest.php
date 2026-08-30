<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Review;
use Database\Seeders\UserSeeder;
use Database\Seeders\BookSeeder;
use Database\Seeders\ReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    // 1. いいね
    public function test_authenticated_user_can_like_a_review(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);
        $this->seed(ReviewSeeder::class);

        $user = User::first();
        $review = Review::first();

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    // 2. いいね解除（トグル）
    public function test_authenticated_user_can_unlike_a_review(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);
        $this->seed(ReviewSeeder::class);

        $user = User::first();
        $review = Review::first();

        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}