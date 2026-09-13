<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // 通知一覧
    public function index()
    {
        $notifications = Auth::user()->notifications;
        return view('notifications.index', compact('notifications'));
    }

    // 既読にする
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back()->with('success', '通知を既読にしました。');
    }
}
