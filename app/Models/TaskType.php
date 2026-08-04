<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'description', 'default_estimated_duration_minutes',
    'default_priority', 'default_instructions', 'default_checklist_id',
    'before_photo_required', 'after_photo_required', 'minimum_photo_count',
    'approval_required', 'allowed_assignee_types', 'active', 'sort_order',
])]
class TaskType extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function defaultChecklist(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'default_checklist_id');
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'task-type';
        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'default_estimated_duration_minutes' => 'integer',
            'before_photo_required' => 'boolean',
            'after_photo_required' => 'boolean',
            'minimum_photo_count' => 'integer',
            'approval_required' => 'boolean',
            'allowed_assignee_types' => 'array',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
