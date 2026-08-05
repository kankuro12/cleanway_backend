<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id);

        // Parallel to the web inbox tabs: ?read=1 → read feed, ?read=0 → unread.
        if ($request->boolean('read')) {
            $notifications->whereNotNull('read_at');
        } elseif ($request->has('read')) {
            $notifications->whereNull('read_at');
        }

        $notifications = $notifications->orderByDesc('id')->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => $notifications->map(fn (Notification $n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'payload' => $n->payload,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at?->toIso8601String(),
            ]),
            'meta' => ['pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
            ]],
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->markRead();

        return response()->json(['data' => ['id' => $notification->id, 'read' => true]]);
    }
}
