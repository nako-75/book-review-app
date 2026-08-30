<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     */

    //プロパティ代入テスト
    public function test_review_model_has_fillable_properties(): void
    {
        $review = new Review([
            'comment' => 'テストコメント',
            'rating' => 5,
        ]);

        $this->assertEquals('テストコメント', $review->comment);
        $this->assertEquals(5, $review->rating);
    }

    //fillable代入テスト‚
    public function test_it_has_fillable_attributes(): void
    {
        $review = new Review([
            'user_id' => 1,
            'book_id' => 2,
            'comment' => '最高の書籍でした！',
            'rating' => 5,
        ]);

        $this->assertEquals(1, $review->user_id);
        $this->assertEquals(2, $review->book_id);
        $this->assertEquals('最高の書籍でした！', $review->comment);
        $this->assertEquals(5, $review->rating);
    }

    //ユーザーリレーションテスト
    public function test_it_belongs_to_a_user(): void
    {
        $review = new Review();
        $relation = $review->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }

    //書籍リレーションテスト
    public function test_it_belongs_to_a_book(): void
    {
        $review = new Review();
        $relation = $review->book();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('book_id', $relation->getForeignKeyName());
    }

    //いいねリレーションテスト（レビュー）
    public function test_it_has_liked_reviews_relation(): void
    {
        $review = new Review();
        $relation = $review->likedReviews();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('review_likes', $relation->getTable());
    }

    //いいねリレーションテスト（ユーザー）
    public function test_it_has_liked_by_users_relation(): void
    {
        $review = new Review();
        $relation = $review->likedByUsers();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('review_likes', $relation->getTable());
    }

    //いいねリレーション（中間テーブル）
    public function test_it_has_review_likes_relation(): void
    {
        $review = new Review();
        $relation = $review->review_likes();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('review_likes', $relation->getTable());
    }
}
