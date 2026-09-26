<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Review
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property string|null $comment
 * @property int $rating
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Book $book
 * @property-read Collection<int, User> $likedReviews
 * @property-read Collection<int, User> $likedByUsers
 * @property-read Collection<int, User> $review_likes
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'comment',
        'rating',
    ];

    /**
     * レビューを投稿したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * レビュー対象の書籍
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * レビューにいいねしたユーザー一覧
     */
    public function likedReviews(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes', 'review_id', 'user_id')->withTimestamps();
    }

    /**
     * レビューにいいねしたユーザー一覧（エイリアス）
     */
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes');
    }

    /**
     * レビューのいいねリレーション
     */
    public function review_likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes', 'review_id', 'user_id')->withTimestamps();
    }
}
