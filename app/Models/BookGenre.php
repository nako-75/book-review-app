<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\BookGenre
 *
 * @property int $id
 * @property int $book_id
 * @property int $genre_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BookGenre extends Model
{
    use HasFactory;
}
