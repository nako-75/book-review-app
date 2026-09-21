<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ReadingPlan;
use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;

class ReadingPlanController extends Controller
{
    // 読書計画一覧
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = ReadingPlan::where('user_id', $userId)->with('book');

        // フィルタリング
        $currentStatus = $request->input('status');
        if (!empty($currentStatus)) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->orderBy('target_date', 'asc')->paginate(10)->withQueryString();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    // 新規作成画面
    public function create()
    {
        $books = Book::all();
        return view('reading-plans.create', compact('books'));
    }

    // 新規登録の保存
    public function store(StoreReadingPlanRequest $request)
    {
        ReadingPlan::create([
            'user_id' => $request->user()->id,
            'book_id' => $request->book_id,
            'target_date' => $request->target_date,
            'status' => ReadingPlanStatus::Reading,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を作成しました！');
    }

    // 編集画面
    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);
        $books = Book::all();
        return view('reading-plans.edit', compact('readingPlan', 'books'));
    }

    // 編集内容の更新
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->target_date,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました！');
    }

    // 完了にする更新
    public function complete(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を完了にしました！');
    }

    // 削除処理
    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);
        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました！');
    }
}
