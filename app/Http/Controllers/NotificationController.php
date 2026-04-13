<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Đánh dấu 1 thông báo là đã đọc và chuyển hướng đến URL của thông báo đó (nếu có).
     */
    public function read($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Nếu thông báo có lưu url thì chuyển hướng tới đó
        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        return back();
    }

    /**
     * Đánh dấu toàn bộ thông báo của user là đã đọc.
     */
    public function readAll()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Đã đánh dấu tất cả là đã đọc.');
    }
}
