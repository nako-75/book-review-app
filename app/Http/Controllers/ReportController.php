<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * マイ読書レポート（統計・集計データ）を表示する
     */
    public function index(): View
    {
        $userId = Auth::id();
        $userReviews = Review::where('user_id', $userId);

        // 1. 基本サマリー
        $totalReviews = (clone $userReviews)->count();
        $booksRead = (clone $userReviews)->distinct('book_id')->count('book_id');
        $averageRating = (clone $userReviews)->avg('rating') ?? 0;

        // 2. 評価
        $ratingDistribution = collect(range(1, 5))->map(function ($i) use ($userId) {
            return Review::where('user_id', $userId)->where('rating', $i)->count();
        });

        // 3. 高評価書籍 TOP5
        $topRatedReviews = Review::where('user_id', $userId)
            ->where('rating', '>=', 4)
            ->with('book')
            ->orderByDesc('rating')
            ->take(5)
            ->get();

        $topRatedBooks = $topRatedReviews->map(function ($review) {
            return [
                'id' => $review->book->id ?? null,
                'title' => $review->book->title ?? '',
                'author' => $review->book->author ?? '',
                'rating' => $review->rating,
            ];
        })->filter(fn ($item) => $item['id'] !== null)->values();

        // 4. ジャンル別評価傾向 TOP5
        $genres = Genre::with(['books.reviews' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])->get();

        $genreRatings = $genres->map(function ($genre) {
            $reviews = $genre->books->flatMap->reviews;
            $count = $reviews->count();
            $avgRating = $count > 0 ? $reviews->avg('rating') : 0;

            return [
                'id' => $genre->id,
                'name' => $genre->name,
                'count' => $count,
                'average_rating' => $avgRating,
            ];
        })->filter(fn ($item) => $item['count'] > 0)
            ->sortByDesc('average_rating')
            ->take(5)
            ->values();

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
