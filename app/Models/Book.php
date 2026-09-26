<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Book
 *
 * @property int $id
 * @property string $title
 * @property string $author
 * @property float|null $price
 * @property int|null $genre_id
 * @property int $user_id
 * @property string|null $isbn
 * @property Carbon|null $published_date
 * @property string|null $description
 * @property string|null $image_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, Genre> $genres
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, Favorite> $favoriteBooks
 */
class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'price',
        'genre_id',
        'user_id',
        'isbn',
        'published_date',
        'description',
        'image_url',
        'created_at',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    /**
     * 登録したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 紐づくジャンル一覧
     */
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    /**
     * 書籍に対するレビュー一覧
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * 書籍のお気に入り登録一覧
     */
    public function favoriteBooks(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }
}
