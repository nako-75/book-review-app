<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RankingController extends Controller
{
    // ランキング
    public function index()
    {
        return view('ranking.index');
    }
}
