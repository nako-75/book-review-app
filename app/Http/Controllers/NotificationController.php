<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * ログインユーザーの通知一覧を表示する
     */
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $notifications = $user->notifications()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 指定された通知を既読にする
     *
     * @param  string  $id
     */
    public function markAsRead($id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', '通知を既読にしました。');
    }
}
