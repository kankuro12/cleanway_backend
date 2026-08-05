<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->unread()
            ->orderByDesc('id')
            ->paginate(30);

        return view('pages.notifications', ['notifications' => $notifications]);
    }

    /**
     * Read feed — rendered rows for the Read tab, fetched lazily via AJAX.
     */
    public function readFeed(Request $request): \Illuminate\Http\JsonResponse
    {
        $items = Notification::where('user_id', $request->user()->id)
            ->whereNotNull('read_at')
            ->orderByDesc('read_at')
            ->paginate(30);

        return response()->json([
            'data' => $items->map(fn (Notification $n) => view('partials.notification-item', ['notification' => $n])->render()),
            'next' => $items->nextPageUrl(),
            'total' => $items->total(),
        ]);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markRead();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'id' => $notification->id]);
        }

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $count = Notification::where('user_id', $request->user()->id)->unread()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'count' => $count]);
        }

        return back()->with('status', 'All notifications marked as read.');
    }
}
