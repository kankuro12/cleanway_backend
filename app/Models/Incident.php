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
    'uuid', 'task_id', 'property_id', 'reporter_id', 'category', 'severity',
    'description', 'latitude', 'longitude', 'status', 'assigned_reviewer_id',
    'resolution', 'resolved_at',
])]
class Incident extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const CATEGORIES = [
        'property_access_problem', 'missing_key', 'incorrect_access_code',
        'damaged_equipment', 'property_damage', 'safety_hazard',
        'missing_supplies', 'unsafe_situation', 'task_cannot_be_completed', 'other',
    ];

    public const SEVERITIES = ['low', 'medium', 'high', 'critical'];

    public const STATUS_OPEN = 'open';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    public const STATUS_INVESTIGATING = 'investigating';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_ACKNOWLEDGED,
        self::STATUS_INVESTIGATING,
        self::STATUS_RESOLVED,
        self::STATUS_CLOSED,
    ];

    protected static function booted(): void
    {
        static::creating(function (Incident $incident): void {
            $incident->uuid ??= (string) Str::uuid();
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(IncidentEvidence::class);
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'resolved_at' => 'datetime',
        ];
    }
}
