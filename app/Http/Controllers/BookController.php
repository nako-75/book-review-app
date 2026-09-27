<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    /**
     * 書籍一覧を表示する
     */
    public function index(Request $request): View
    {
        $query = Book::query();

        if ($keyword = $request->input('keyword')) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        // ジャンルフィルタ
        if ($genreId = $request->input('genre')) {
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        // ソート
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $books = $query->with('genres')
            ->withAvg('reviews', 'rating')
            ->paginate(10)
            ->withQueryString();

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    /**
     * 書籍の詳細を表示する
     */
    public function show(Book $book): View
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍登録画面を表示する
     */
    public function create(): View
    {
        $this->authorize('create', Book::class);
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    /**
     * 書籍を新規登録する
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $this->authorize('create', Book::class);

        DB::transaction(function () use ($request) {
            // 書籍本体を作成
            $book = Book::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);
            $book->genres()->sync($request->input('genres'));
        });

        return redirect()->route('books.index')->with('success', '書籍を登録しました！');
    }

    /**
     * 書籍編集画面を表示する
     */
    public function edit(Book $book): View
    {
        $this->authorize('update', $book);
        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * 書籍情報を更新する
     */
    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        DB::transaction(function () use ($request, $book) {
            // 書籍情報を更新
            $book->update($request->validated());

            $book->genres()->sync($request->input('genres'));
        });

        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました！');
    }

    /**
     * 書籍を削除する
     */
    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);
        DB::transaction(function () use ($book) {
            $book->delete();
        });

        return redirect()->route('books.index')->with('success', '書籍を削除しました！');
    }

    /**
     * 指定されたISBNコードを基にGoogle Books APIから書籍情報を取得する
     */
    public function fetchBookByIsbn(string $isbn): JsonResponse
    {
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:'.$isbn,
            'key' => config('services.google_books.key'),
        ]);

        if ($response->successful() && isset($response->json()['items'][0])) {
            $volumeInfo = $response->json()['items'][0]['volumeInfo'];

            return response()->json([
                'title' => $volumeInfo['title'] ?? '',
                'author' => isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : '',
                'description' => $volumeInfo['description'] ?? '',
                'image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? '',
                'published_date' => $volumeInfo['publishedDate'] ?? '',
            ]);
        }

        return response()->json(['error' => '該当する書籍が見つかりませんでした。'], 404);
    }
}
