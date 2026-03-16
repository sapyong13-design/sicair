<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->update(['is_read' => true]);
        Cache::forget('notif_unread_' . Auth::id());

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }

    public function markAllRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
        Cache::forget('notif_unread_' . Auth::id());

        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function unreadCount()
    {
        $count = Cache::remember('notif_unread_' . Auth::id(), 60,
            fn() => Notification::where('user_id', Auth::id())->where('is_read', false)->count()
        );

        return response()->json(['count' => $count]);
    }
}
