<?php

namespace App\Models;

use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\ReadingPlan
 *
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property Carbon $target_date
 * @property ReadingPlanStatus $status
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Book $book
 * @property-read User $user
 */
class ReadingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'target_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_at' => 'datetime',
        'status' => ReadingPlanStatus::class,
    ];

    /**
     * 読書計画に紐づく書籍
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * 読書計画を作成したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
