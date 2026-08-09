<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // 書籍詳細からの新規投稿
    public function store(Request $request, Book $book)
    {
        $book->reviews()->create([
            'user_id' => Auth::id(),
            'comment' => $request->comment,
            'rating'  => $request->rating,
        ]);
        return back();
    }

    // 編集画面の表示
    public function edit(Review $review)
    {
        $this->authorize('update', $review);
        return view('reviews.edit', compact('review'));
    }

    // 更新処理
    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        // TODO: 後ほど FormRequest に置き換え
        $review->update([
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $review->book_id);
    }
}
