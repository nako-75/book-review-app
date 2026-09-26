<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\Genre
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Book> $books
 */
class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * ジャンルに紐づく書籍一覧
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_genre');
    }
}
