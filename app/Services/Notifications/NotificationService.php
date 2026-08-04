<?php

namespace App\Services\Notifications;

use App\Jobs\SendPushNotification;
use App\Models\Notification;
use App\Models\NotificationDelivery;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Notification writer with idempotency and a per-channel delivery log.
 * - in_app: row in the notifications inbox
 * - email:  queued Mailable when supplied
 * - push:   one queued FCM job per registered device (FirebaseMessenger)
 */
class NotificationService
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<int, string>  $channels
     */
    public function send(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        array $payload = [],
        ?string $idempotencyKey = null,
        array $channels = [NotificationDelivery::CHANNEL_IN_APP],
        ?Mailable $mail = null,
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
            if ($channel === NotificationDelivery::CHANNEL_EMAIL) {
                if ($mail !== null && $user->email) {
                    Mail::to($user)->queue($mail);

                    $this->logDelivery($notification, $channel, NotificationDelivery::STATUS_SENT);
                }

                continue;
            }

            if ($channel === NotificationDelivery::CHANNEL_PUSH) {
                $devices = $user->devices()->get(['fcm_token']);

                if ($devices->isEmpty()) {
                    $this->logDelivery($notification, $channel, NotificationDelivery::STATUS_SKIPPED);

                    continue;
                }

                foreach ($devices as $device) {
                    $delivery = $this->logDelivery($notification, $channel, NotificationDelivery::STATUS_PENDING);

                    SendPushNotification::dispatch($delivery->id, $device->fcm_token, $title, (string) $body, $payload);
                }

                continue;
            }

            $this->logDelivery($notification, $channel, NotificationDelivery::STATUS_SENT);
        }

        return $notification;
    }

    private function logDelivery(Notification $notification, string $channel, string $status): NotificationDelivery
    {
        return NotificationDelivery::create([
            'notification_id' => $notification->id,
            'channel' => $channel,
            'status' => $status,
            'delivered_at' => $status === NotificationDelivery::STATUS_PENDING ? null : now(),
        ]);
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
