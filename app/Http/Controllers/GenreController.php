<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    // ジャンル一覧
    public function index()
    {
        $genres = Genre::withCount('books')->get();
        return view('genres.index', compact('genres'));
    }

    // ジャンル登録画面
    public function create()
    {
        return view('genres.create');
    }

    // ジャンル詳細
    public function show(Genre $genre)
    {
        $books = $genre->books()->paginate(10);
        return view('genres.show', compact('genre', 'books'));
    }

    // ジャンル登録
    public function store(Request $request)
    {
        Genre::create([
            'name' => $request->name,
        ]);
        return redirect()->route('genres.index');
    }

    // ジャンル編集画面
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $genre->update([
            'name' => $request->name,
        ]);
        return redirect()->route('genres.index');
    }

    // ジャンル削除
    public function destroy(Genre $genre)
    {
        $genre->delete();
        return redirect()->route('genres.index');
    }
}
