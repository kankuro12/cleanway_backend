<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'notification_id', 'channel', 'status', 'attempts', 'delivered_at', 'error',
])]
class NotificationDelivery extends Model
{
    public const CHANNEL_IN_APP = 'in_app';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_PUSH = 'push';

    public const CHANNEL_SMS = 'sms';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'delivered_at' => 'datetime',
        ];
    }
}
