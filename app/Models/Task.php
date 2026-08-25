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
    'uuid', 'reference_number', 'title', 'description', 'task_type_id',
    'property_id', 'property_name_snapshot', 'address_snapshot',
    'latitude_snapshot', 'longitude_snapshot', 'check_in_radius_snapshot',
    'assigned_manager_id', 'scheduled_start_at', 'scheduled_end_at',
    'estimated_duration_minutes', 'worked_seconds', 'last_resume_at', 'hourly_rate', 'parking_fee', 'extra_payments', 'priority', 'status', 'recurrence_rule',
    'approval_required', 'task_type_snapshot', 'accepted_at', 'started_at',
    'completed_at', 'submitted_at', 'approved_at', 'rejected_at', 'cancelled_at',
    'created_by', 'updated_by',
])]
class Task extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_UNASSIGNED = 'unassigned';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_DELAYED = 'delayed';

    public const STATUS_UNABLE_TO_ACCESS = 'unable_to_access';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SUBMITTED_FOR_APPROVAL = 'submitted_for_approval';

    public const STATUS_CORRECTION_REQUESTED = 'correction_requested';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REOPENED = 'reopened';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_UNASSIGNED,
        self::STATUS_ASSIGNED,
        self::STATUS_ACCEPTED,
        self::STATUS_DECLINED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_PAUSED,
        self::STATUS_DELAYED,
        self::STATUS_UNABLE_TO_ACCESS,
        self::STATUS_COMPLETED,
        self::STATUS_SUBMITTED_FOR_APPROVAL,
        self::STATUS_CORRECTION_REQUESTED,
        self::STATUS_REJECTED,
        self::STATUS_REOPENED,
        self::STATUS_APPROVED,
        self::STATUS_CANCELLED,
    ];

    public const PRIORITIES = ['low', 'medium', 'high', 'critical'];

    public const ASSIGNMENT_PENDING = 'pending';

    public const ASSIGNMENT_ACCEPTED = 'accepted';

    public const ASSIGNMENT_DECLINED = 'declined';

    protected static function booted(): void
    {
        static::creating(function (Task $task): void {
            $task->uuid ??= (string) Str::uuid();
            $task->reference_number ??= 'T-'.date('ymd').'-'.strtoupper(Str::random(5));
        });
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskStatusHistory::class)->orderByDesc('id');
    }

    public function checklistSnapshot(): HasMany
    {
        return $this->hasMany(TaskChecklistSnapshot::class)->orderBy('sort_order');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(TaskEvidence::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TaskApproval::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest('id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(TaskSubtask::class)->orderBy('sort_order');
    }

    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigneeUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments', 'task_id', 'assignee_id')
            ->wherePivot('assignee_type', 'user');
    }

    public function scopeForUser($query, User $user): void
    {
        $query->whereHas('assignments', fn ($q) => $q->where('assignee_type', 'user')->where('assignee_id', $user->id));
    }

    /**
     * @return array<int, string>
     */
    public function transitionableStatuses(): array
    {
        return \App\Domain\Tasks\TransitionTaskStatus::TRANSITIONS[$this->status] ?? [];
    }

    public static function getSimplifiedStatuses(): array
    {
        return [
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public function getSimplifiedStatusAttribute(): string
    {
        if (in_array($this->status, ['in_progress', 'paused', 'delayed'], true)) {
            return 'in_progress';
        }
        if (in_array($this->status, ['completed', 'submitted_for_approval', 'approved'], true)) {
            return 'completed';
        }
        if (in_array($this->status, ['cancelled', 'declined', 'rejected'], true)) {
            return 'cancelled';
        }
        return 'not_started';
    }

    public function scopeFilter($query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, function ($q, $v) {
                if ($v === 'not_started') {
                    $q->whereIn('status', ['not_started', 'draft', 'scheduled', 'unassigned', 'assigned', 'accepted']);
                } elseif ($v === 'in_progress') {
                    $q->whereIn('status', ['in_progress', 'paused', 'delayed']);
                } elseif ($v === 'completed') {
                    $q->whereIn('status', ['completed', 'submitted_for_approval', 'approved']);
                } elseif ($v === 'cancelled') {
                    $q->whereIn('status', ['cancelled', 'declined', 'rejected']);
                } else {
                    $q->where('status', $v);
                }
            })
            ->when($filters['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))
            ->when($filters['task_type_id'] ?? null, fn ($q, $v) => $q->where('task_type_id', $v))
            ->when($filters['property_id'] ?? null, fn ($q, $v) => $q->where('property_id', $v))
            ->when($filters['assignee_id'] ?? null, fn ($q, $v) => $q->whereHas('assignments', fn ($q) => $q->where('assignee_id', $v)))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->where('scheduled_start_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->where('scheduled_start_at', '<=', $v));
    }

    protected function casts(): array
    {
        return [
            'latitude_snapshot' => 'float',
            'longitude_snapshot' => 'float',
            'check_in_radius_snapshot' => 'integer',
            'estimated_duration_minutes' => 'integer',
            'worked_seconds' => 'integer',
            'last_resume_at' => 'datetime',
            'hourly_rate' => 'float',
            'parking_fee' => 'float',
            'extra_payments' => 'array',
            'approval_required' => 'boolean',
            'task_type_snapshot' => 'array',
            'scheduled_start_at' => 'datetime',
            'scheduled_end_at' => 'datetime',
            'accepted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }
}
