<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    // 書籍一覧（トップ）
    public function index()
    {
        $books = Book::latest()->paginate(10);
        return view('books.index', compact('books'));
    }

    // 書籍詳細
    public function show($id)
    {
        $book = Book::with([
            'genres',
            'reviews.user',
            'reviews.likedByUsers'
        ])->findOrFail($id);

        return view('books.show', compact('book'));
    }

    // 書籍登録画面
    public function create()
    {
        $genres = Genre::all();
        return view('books.create', compact('genres'));
    }

    public function store(Request $request)
    {
        Book::create($request->all());
        return redirect()->route('books.index')->with('success', '書籍を登録しました！');
    }

    // 書籍編集画面
    public function edit($book)
    {
        return view('books.edit', compact('book'));
    }
}
