<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Requests\Api\IndexBookRequest;
use App\Http\Resources\Api\V1\BookResource;

class BookController extends Controller
{
    // 書籍一覧
    public function index(IndexBookRequest $request)
    {
        $validated = $request->validated();

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if (!empty($validated['genre_id'])) {
            $query->whereHas('genres', function ($q) use ($validated) {
                $q->where('genres.id', $validated['genre_id']);
            });
        }

        $perPage = $validated['per_page'] ?? 10;
        $books = $query->paginate($perPage);
        return BookResource::collection($books);
    }

    // 書籍詳細
    public function show(Book $book)
    {
        $book->load('genres', 'reviews.user');
        return new BookResource($book);
    }

    // 書籍登録
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();
        $book = Book::create($validated);
        if (isset($validated['genres'])) {
            $book->genres()->sync($validated['genres']);
        }
        $book->load('genres');
        return (new BookResource($book))->response()->setStatusCode(201);
    }

    // 書籍更新
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();

        $book->update($validated);
        if (isset($validated['genres'])) {
            $book->genres()->sync($validated['genres']);
        }
        $book->load('genres');
        return new BookResource($book);
    }

    // 書籍削除
    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
