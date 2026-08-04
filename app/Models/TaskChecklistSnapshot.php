<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id', 'section_name', 'item_label', 'item_type', 'required', 'issue_triggering', 'sort_order',
])]
class TaskChecklistSnapshot extends Model
{
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'issue_triggering' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
