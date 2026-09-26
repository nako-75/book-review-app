<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\View\View;

class RankingController extends Controller
{
    /**
     * レビュー評価に基づく書籍ランキングを表示する
     */
    public function index(): View
    {
        $rankedBooks = Book::has('reviews')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('reviews_avg_rating', 'desc')
            ->take(10)
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
