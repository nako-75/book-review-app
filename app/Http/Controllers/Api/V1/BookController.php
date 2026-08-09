<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // 書籍一覧
    public function index(Request $request)
    {
        $books = Book::with('genres', 'reviews')->paginate(10);
        return response()->json($books);
    }

    // 書籍詳細
    public function show(Book $book)
    {
        $book->load('genres', 'reviews.user');
        return response()->json($book);
    }

    // 書籍登録
    public function store(Request $request)
    {
        $book = Book::create($request->all());
        return response()->json($book, 201);
    }

    // 書籍更新
    public function update(Request $request, Book $book)
    {
        $book->update($request->all());
        return response()->json($book);
    }

    // 書籍削除
    public function destroy(Book $book)
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
