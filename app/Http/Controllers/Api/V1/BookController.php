<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexBookRequest;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * 書籍一覧を取得する（API）
     */
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (! empty($validated['keyword'])) {
            $keyword = $validated['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if (! empty($validated['genre_id'])) {
            $query->whereHas('genres', function ($q) use ($validated) {
                $q->where('genres.id', $validated['genre_id']);
            });
        }

        $perPage = $validated['per_page'] ?? 10;
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * 指定された書籍の詳細情報を取得する（API）
     */
    public function show(Book $book): BookResource
    {
        $book->load('genres', 'reviews.user');

        return new BookResource($book);
    }

    /**
     * 書籍を新規登録する（API）
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $this->authorize('create', Book::class);

        $validated = $request->validated();
        /** @var User $user */
        $user = $request->user();
        $validated['user_id'] = $user->id;

        $book = Book::create($validated);
        if (isset($validated['genres'])) {
            $book->genres()->sync($validated['genres']);
        }
        $book->load('genres');

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    /**
     * 書籍情報を更新する（API）
     */
    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update($validated);
        if (isset($validated['genres'])) {
            $book->genres()->sync($validated['genres']);
        }
        $book->load('genres');

        return new BookResource($book);
    }

    /**
     * 書籍を削除する（API）
     */
    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
