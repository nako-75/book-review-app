<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Requests\Api\IndexBookRequest;

class BookController extends Controller
{
    // 書籍一覧
    public function index(IndexBookRequest $request)
    {
        $validated = $request->validated();

        $query = Book::with('genres', 'reviews');

        if (!empty($validated['keyword'])) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ;($q->where('author', 'like', "%{$keyword}%")); // または author
            });
        }

        if (!empty($validated['genre_id'])) {
            $query->whereHas('genres', function ($q) use ($validated) {
                $q->where('genres.id', $validated['genre_id']);
            });
        }

        $perPage = $validated['per_page'] ?? 10;
        $books = $query->paginate($perPage);
        return response()->json($books);
    }

    // 書籍詳細
    public function show(Book $book)
    {
        $book->load('genres', 'reviews.user');
        return response()->json($book);
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
        return response()->json($book, 201);
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
        return response()->json($book);
    }

    // 書籍削除
    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
