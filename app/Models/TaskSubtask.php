<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id', 'title', 'section_name', 'completed_at', 'completed_by', 'sort_order',
])]
class TaskSubtask extends Model
{
    use Auditable;

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }
}
