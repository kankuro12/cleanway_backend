<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'rule', 'start_date', 'end_date', 'time', 'property_id',
    'assignee_type', 'assignee_id', 'task_type_id', 'checklist_template_id',
    'notification_minutes_before', 'active', 'created_by',
])]
class TaskRecurrence extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'active' => 'boolean',
            'notification_minutes_before' => 'integer',
        ];
    }
}
