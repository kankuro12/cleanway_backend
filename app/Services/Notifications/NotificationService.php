<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * In-app notification writer with idempotency and a delivery log per channel.
 * Channels beyond in_app are stubs: mark skipped so the delivery trail exists
 * and later mail/push/SMS slots drop in behind the same rows.
 */
class NotificationService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function send(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        array $payload = [],
        ?string $idempotencyKey = null,
        array $channels = [NotificationDelivery::CHANNEL_IN_APP],
    ): ?Notification {
        if ($idempotencyKey !== null && Notification::where('idempotency_key', $idempotencyKey)->exists()) {
            return null;
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'payload' => $payload,
            'idempotency_key' => $idempotencyKey ?? Str::uuid()->toString(),
        ]);

        foreach ($channels as $channel) {
            NotificationDelivery::create([
                'notification_id' => $notification->id,
                'channel' => $channel,
                'status' => $channel === NotificationDelivery::CHANNEL_IN_APP
                    ? NotificationDelivery::STATUS_SENT
                    : NotificationDelivery::STATUS_SKIPPED,
                'delivered_at' => now(),
            ]);
        }

        return $notification;
    }

    public function notifyTaskAssignees(\App\Models\Task $task, string $type, string $title, string $body, array $payload = []): void
    {
        foreach ($task->assignments()->with('assignee')->get() as $assignment) {
            $assignee = $assignment->assignee;

            if ($assignee instanceof User) {
                $this->send($assignee, $type, $title, $body, $payload, "{$type}:{$task->id}:{$assignee->id}");
            }
        }
    }
}
