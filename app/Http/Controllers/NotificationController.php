<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * 通知一覧を表示する
     *
     * @return View 通知一覧画面
     */
    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    /**
     * 通知を既読にする
     *
     * @param  string  $id  既読対象の通知ID
     * @return RedirectResponse 通知一覧画面へリダイレクト
     */
    public function read(string $id): RedirectResponse
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return redirect()->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
