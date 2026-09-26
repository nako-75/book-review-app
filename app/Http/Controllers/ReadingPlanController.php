<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧を表示する
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $query = ReadingPlan::where('user_id', $userId)->with('book');

        // フィルタリング
        $currentStatus = $request->input('status');
        if (! empty($currentStatus)) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->orderBy('target_date', 'asc')->paginate(10)->withQueryString();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画新規作成画面を表示する
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新規の読書計画を登録する
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => $request->user()->id,
            'book_id' => $request->book_id,
            'target_date' => $request->target_date,
            'status' => ReadingPlanStatus::Reading,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました！');
    }

    /**
     * 読書計画編集画面を表示する
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);
        $books = Book::all();

        return view('reading-plans.edit', compact('readingPlan', 'books'));
    }

    /**
     * 読書計画の内容を更新する
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->target_date,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました！');
    }

    /**
     * 読書計画を完了状態に更新する
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了にしました！');
    }

    /**
     * 読書計画を削除する
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);
        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました！');
    }
}
