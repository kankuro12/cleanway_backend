<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Row-level visibility per role.
 *
 * - admin: all users
 * - supervisor: own branch, own team, directly managed cleaners
 * - cleaner: self only
 */
class PersonnelScope
{
    public static function apply(Builder $query, User $user): Builder
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return $query;
        }

        if ($user->hasRole(User::ROLE_SUPERVISOR)) {
            return $query->where(function (Builder $q) use ($user): void {
                $q->whereKey($user->id)
                    ->when($user->branch_id, fn (Builder $q, $branchId) => $q->orWhere('branch_id', $branchId))
                    ->when($user->team_id, fn (Builder $q, $teamId) => $q->orWhere('team_id', $teamId))
                    ->orWhere('manager_id', $user->id);
            });
        }

        // Cleaner: self only.
        return $query->whereKey($user->id);
    }
}
