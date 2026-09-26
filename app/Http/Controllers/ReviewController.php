<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * 書籍に対する新しいレビューを登録する
     */
    public function store(StoreReviewRequest $request, Book $book): RedirectResponse
    {
        $validated = $request->validated();

        $book->reviews()->create([
            'user_id' => auth()->id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return back();
    }

    /**
     * レビュー編集画面を表示する
     */
    public function edit(Review $review): View
    {
        $this->authorize('update', $review);

        return view('reviews.edit', compact('review'));
    }

    /**
     * レビューを更新する
     */
    public function update(UpdateReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);
        $validated = $request->validated();
        $review->update($validated);

        return redirect()->route('books.show', $review->book_id);
    }

    /**
     * レビューを削除する
     */
    public function destroy(Review $review): RedirectResponse
    {
        $this->authorize('delete', $review);
        DB::transaction(function () use ($review) {
            $review->delete();
        });

        return redirect()->route('books.show', $review->book_id);
    }

    /**
     * レビューのいいね・いいね解除を切り替える
     */
    public function like(Review $review, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->likedReviews()->toggle($review->id);

        return back();
    }
}
