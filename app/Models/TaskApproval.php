<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id', 'action', 'reviewer_id', 'previous_status', 'remarks',
    'reason_code', 'requested_corrections', 'quality_score',
])]
class TaskApproval extends Model
{
    use Auditable;

    public const ACTION_APPROVE = 'approve';

    public const ACTION_REJECT = 'reject';

    public const ACTION_REQUEST_CORRECTION = 'request_correction';

    public const ACTION_REOPEN = 'reopen';

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    protected function casts(): array
    {
        return ['quality_score' => 'integer'];
    }
}
