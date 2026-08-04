<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'property_id', 'assignable_type', 'assignable_id', 'assignment_role',
    'start_date', 'end_date', 'is_primary', 'assigned_by', 'reason',
])]
class PropertyAssignment extends Model
{
    use Auditable;

    public const ROLE_MANAGER = 'manager';

    public const ROLE_SUPERVISOR = 'supervisor';

    public const ROLE_CLEANER = 'cleaner';

    public const ROLE_TEAM = 'team';

    public const ROLE_BRANCH = 'branch';

    public const ROLE_SERVICE_AREA = 'service_area';

    public const ROLES = [
        self::ROLE_MANAGER,
        self::ROLE_SUPERVISOR,
        self::ROLE_CLEANER,
        self::ROLE_TEAM,
        self::ROLE_BRANCH,
        self::ROLE_SERVICE_AREA,
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query): void
    {
        $query->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()->toDateString()));
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_primary' => 'boolean',
        ];
    }
}
