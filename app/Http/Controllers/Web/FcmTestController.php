<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Models\Team;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Ghost test page for FCM push (no sidebar link). Admin-only.
 */
class FcmTestController extends Controller
{
    public function index(): View
    {
        $users = User::withCount('devices')->orderBy('name')->get(['id', 'name', 'email', 'role', 'status']);

        return view('pages.fcm-test', [
            'users' => $users,
            'teams' => Team::withCount(['members'])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function send(Request $request, NotificationService $notifications): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'recipient' => ['required', 'in:user,team,role'],
            'user_id' => ['required_if:recipient,user', 'exists:users,id'],
            'team_id' => ['required_if:recipient,team', 'exists:teams,id'],
            'role' => ['required_if:recipient,role', 'in:0,1,2'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $targets = match ($request->string('recipient')->toString()) {
            'user' => collect([User::findOrFail($request->integer('user_id'))]),
            'team' => Team::findOrFail($request->integer('team_id'))->members()->get(),
            default => User::where('role', $request->integer('role'))->get(),
        };

        $sent = 0;
        $pushed = 0;
        $skipped = 0;
        $keyBase = 'fcm.test:'.Str::uuid();

        foreach ($targets as $user) {
            $notification = $notifications->send(
                $user,
                'fcm.test',
                $request->string('title'),
                $request->string('body'),
                ['test' => true, 'channel' => 'fcm'],
                "{$keyBase}:{$user->id}",
                [NotificationDelivery::CHANNEL_IN_APP, NotificationDelivery::CHANNEL_PUSH],
            );

            if (! $notification) {
                continue;
            }

            $sent++;

            $push = $notification->deliveries()->where('channel', NotificationDelivery::CHANNEL_PUSH)->first();

            if ($push && $push->status === NotificationDelivery::STATUS_PENDING) {
                $pushed++;
            } elseif ($push && $push->status === NotificationDelivery::STATUS_SKIPPED) {
                $skipped++;
            }
        }

        $message = "Sent to {$sent} user(s): {$pushed} with FCM push, {$skipped} without devices.";

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('fcm-test')->with('status', $message);
    }
}
