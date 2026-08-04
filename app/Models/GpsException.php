<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'event_id', 'task_id', 'policy', 'reason', 'integrity_flags',
    'resolved_at', 'resolved_by', 'resolution_remarks',
])]
class GpsException extends Model
{
    use Auditable;

    public const POLICY_ACCEPT = 'accept';

    public const POLICY_EXCEPTION = 'exception';

    public const POLICY_OVERRIDE = 'override';

    public const POLICY_REJECT = 'reject';

    public function event(): BelongsTo
    {
        return $this->belongsTo(AttendanceEvent::class, 'event_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    protected function casts(): array
    {
        return [
            'integrity_flags' => 'array',
            'resolved_at' => 'datetime',
        ];
    }
}
