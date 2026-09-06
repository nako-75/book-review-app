<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    // 書籍一覧（トップ）
    public function index(Request $request)
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

    // 書籍詳細
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers'
        ]);

        return view('books.show', compact('book'));
    }

    // 書籍登録画面
    public function create()
    {
        $this->authorize('create', Book::class);
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    // 書籍登録
    public function store(StoreBookRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = Auth::id();
        Book::create($data);
        return redirect()->route('books.index');
    }

    // 書籍編集画面
    public function edit(Book $book)
    {
        $this->authorize('update', $book);
        $genres = Genre::all();
        return view('books.edit', compact('book','genres'));
    }

    // 書籍更新処理
    public function update(UpdateBookRequest $request, Book $book)
    {
        $validated = $request->validated();
        $this->authorize('update', $book);
        $book->update($request->all());
        return redirect()->route('books.show', $book)->with('success', '書籍を更新しました！');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);
        $book->delete();
        return redirect()->route('books.index')->with('success', '書籍を削除しました！');
    }
}
