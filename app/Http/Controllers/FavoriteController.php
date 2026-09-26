<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    /**
     * お気に入り登録された書籍の一覧を表示する
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $books = $user->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    /**
     * 書籍のお気に入り登録・解除を切り替える
     */
    public function toggle(Book $book): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->favoriteBooks()->toggle($book->id);

        return back();
    }
}
