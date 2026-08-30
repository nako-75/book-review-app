<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Database\Seeders\BookSeeder;
use Database\Seeders\ReviewSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    // ランキング表示
    public function test_ranking_page_can_be_rendered(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);
        $this->seed(ReviewSeeder::class);

        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }

    // 並び順
    public function test_ranking_is_ordered_by_average_rating(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(BookSeeder::class);
        $this->seed(ReviewSeeder::class);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            $ratings = $rankedBooks->pluck('reviews_avg_rating')->toArray();
            $sortedRatings = collect($ratings)->sortDesc()->values()->toArray();

            return $ratings === $sortedRatings;
        });
    }
}