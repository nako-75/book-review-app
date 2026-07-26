<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        return view('favorites.index');
    }

    public function toggle(Book $book)
    {
        $user = Auth::user();
        $user->favoriteBooks()->toggle($book->id);
        return back();
    }
}
