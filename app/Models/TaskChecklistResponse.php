<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id', 'snapshot_item_id', 'value', 'answered_by', 'answered_at',
])]
class TaskChecklistResponse extends Model
{
    use Auditable;

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function answeredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'answered_by');
    }

    protected function casts(): array
    {
        return ['answered_at' => 'datetime'];
    }
}
