<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;

class ReviewController extends Controller
{
    // 書籍詳細からの新規投稿
    public function store(StoreReviewRequest $request, Book $book)
    {
        $validated = $request->validated();

        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
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
    public function update(UpdateReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);
        $validated = $request->validated();
        $review->update($validated);
        return redirect()->route('books.show', $review->book_id);
    }
}
