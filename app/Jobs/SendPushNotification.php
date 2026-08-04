<?php

namespace App\Jobs;

use App\Models\NotificationDelivery;
use App\Services\Notifications\FirebaseMessenger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $deliveryId,
        public readonly string $fcmToken,
        public readonly string $title,
        public readonly string $body,
        public readonly array $data = [],
    ) {}

    public function handle(FirebaseMessenger $messenger): void
    {
        $delivery = NotificationDelivery::find($this->deliveryId);

        if (! $delivery) {
            return;
        }

        $sent = $messenger->send($this->fcmToken, $this->title, $this->body, $this->data);

        $delivery->update([
            'status' => $sent ? NotificationDelivery::STATUS_SENT : NotificationDelivery::STATUS_FAILED,
            'attempts' => $delivery->attempts + 1,
            'delivered_at' => $sent ? now() : $delivery->delivered_at,
            'error' => $sent ? null : 'FCM send failed',
        ]);
    }
}
