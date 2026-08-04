<?php

namespace App\Models;

use App\Support\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password', 'role',
    'employee_no', 'phone', 'profile_image_path', 'emergency_contact',
    'branch_id', 'team_id', 'manager_id', 'employment_type',
    'start_date', 'end_date', 'skills', 'certifications',
    'default_working_hours', 'service_areas', 'notification_preferences', 'status',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Auditable, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /** @use HasFactory<UserFactory> */
    public const ROLE_ADMIN = 0;

    public const ROLE_SUPERVISOR = 1;

    public const ROLE_CLEANER = 2;

    public const STATUS_INVITED = 'invited';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SUSPENDED = 'suspended';

    public const STATUS_ON_LEAVE = 'on_leave';

    public const STATUS_ARCHIVED = 'archived';

    public const ROLES = [self::ROLE_ADMIN, self::ROLE_SUPERVISOR, self::ROLE_CLEANER];

    public const STATUSES = [
        self::STATUS_INVITED,
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
        self::STATUS_ON_LEAVE,
        self::STATUS_ARCHIVED,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'emergency_contact' => 'array',
            'skills' => 'array',
            'certifications' => 'array',
            'default_working_hours' => 'array',
            'service_areas' => 'array',
            'notification_preferences' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function managedUsers(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('role_in_team', 'joined_at', 'left_at')
            ->withTimestamps();
    }

    public function teamMemberships(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function managerAssignments(): HasMany
    {
        return $this->hasMany(ManagerAssignment::class, 'manager_id');
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['search'] ?? null, fn ($q, $search) => $q
            ->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('employee_no', 'like', "%{$search}%")));

        $query->when(isset($filters['role']), fn ($q, $role) => $q->where('role', $role));
        $query->when(isset($filters['status']), fn ($q, $status) => $q->where('status', $status));
        $query->when(isset($filters['branch_id']), fn ($q, $branch) => $q->where('branch_id', $branch));
    }

    public function hasRole(int $role): bool
    {
        return (int) $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(string $permission): bool
    {
        $grants = config("permissions.roles.{$this->role}", []);

        foreach ($grants as $grant) {
            if ($grant === '*') {
                return true;
            }

            // Normalize "1.*" to "1".
            $grant = rtrim($grant, '.*');

            if ($permission === $grant || str_starts_with($permission, $grant.'.')) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
