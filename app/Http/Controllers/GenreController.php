<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GenreController extends Controller
{
    /**
     * ジャンル一覧を表示する
     */
    public function index(): View
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    /**
     * ジャンル登録画面を表示する
     */
    public function create(): View
    {
        return view('genres.create');
    }

    /**
     * ジャンルの詳細とそれに紐づく書籍一覧を表示する
     */
    public function show(Genre $genre): View
    {
        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * ジャンルを新規登録する
     */
    public function store(StoreGenreRequest $request): RedirectResponse
    {
        Genre::create($request->validated());

        return redirect()->route('genres.index');
    }

    /**
     * ジャンル編集画面を表示する
     */
    public function edit(Genre $genre): View
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * ジャンル情報を更新する
     */
    public function update(UpdateGenreRequest $request, Genre $genre): RedirectResponse
    {
        $genre->update($request->validated());

        return redirect()->route('genres.index');
    }

    /**
     * ジャンルを削除する
     */
    public function destroy(Genre $genre): RedirectResponse
    {
        // 紐づく書籍が存在するかチェック
        if ($genre->books()->exists()) {
            return redirect()
                ->route('genres.index')
                ->with('error', 'このジャンルを使用している書籍が存在するため削除できません。');
        }

        // 紐づきがない場合は安全に削除
        DB::transaction(function () use ($genre) {
            $genre->delete();
        });

        return redirect()
            ->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
