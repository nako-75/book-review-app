<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;


class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = Review::all();
        $users = User::all();

        foreach ($reviews as $review) {
            $otherUsers = $users->where('id', '!=', $review->user_id);
            $likeCount = rand(0, min(3, $otherUsers->count()));

            if ($likeCount > 0) {
                $likerIds = $otherUsers->random($likeCount)->pluck('id')->toArray();
                $review->review_likes()->syncWithoutDetaching($likerIds);
            }
        }
    }
}
