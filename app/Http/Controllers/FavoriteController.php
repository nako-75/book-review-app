<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $books = Auth::user()->favoriteBooks()->paginate(10); 
    return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book)
    {
        $user = Auth::user();
        $user->favoriteBooks()->toggle($book->id);
        return back();
    }
}
