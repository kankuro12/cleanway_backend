<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id', 'section_name', 'item_label', 'item_type', 'required',
    'is_photo_required', 'is_comment_required', 'photo_url', 'comment',
    'issue_triggering', 'sort_order', 'completed_at', 'completed_by',
])]
class TaskChecklistSnapshot extends Model
{
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
            'required' => 'boolean',
            'is_photo_required' => 'boolean',
            'is_comment_required' => 'boolean',
            'issue_triggering' => 'boolean',
            'sort_order' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): array {
                if ($value === null || $value === '') {
                    return [];
                }
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
                // Legacy plain string e.g. "/storage/..."
                return [$value];
            },
            set: fn (?array $value) => $value === null ? null : json_encode(array_values($value ?? [])),
        );
    }
}
