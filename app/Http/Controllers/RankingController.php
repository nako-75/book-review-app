<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class RankingController extends Controller
{
    // ランキング
    public function index()
    {
        $rankedBooks = Book::withCount('reviews')
                           ->withAvg('reviews', 'rating')
                           ->orderBy('reviews_avg_rating', 'desc')
                           ->take(10)
                           ->get();
        return view('ranking.index', compact('rankedBooks'));
    }
}
