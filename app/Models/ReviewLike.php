<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ReviewLike
 *
 * @property int $id
 * @property int $user_id
 * @property int $review_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Review $review
 */
class ReviewLike extends Model
{
    use HasFactory;

    protected $table = 'review_likes';

    protected $fillable = [
        'user_id',
        'review_id',
    ];

    /**
     * いいねしたユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * いいねされたレビュー
     */
    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }
}
