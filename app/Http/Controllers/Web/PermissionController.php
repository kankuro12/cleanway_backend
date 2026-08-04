<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Per-user permission fine-tuning (Settings > Roles & Permissions).
 * Overrides sit on top of the role baseline from config/permissions.php.
 */
class PermissionController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role', 'status']);
        $selected = $users->firstWhere('id', $request->integer('user_id')) ?? $users->first();

        return view('pages.permissions', [
            'users' => $users,
            'selected' => $selected,
            'permissionTree' => $this->permissionTree($selected?->role),
            'overrides' => $selected
                ? $selected->permissionOverrides()->get()->keyBy('permission')
                : collect(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'in:grant,deny'],
        ]);

        $rows = [];
        $known = array_keys((array) config('permissions.permissions'));

        foreach ($request->input('permissions', []) as $key => $value) {
            if (! in_array($key, $known, true) || ! $value) {
                continue;
            }

            $rows[] = ['user_id' => $user->id, 'permission' => $key, 'granted' => $value === 'grant'];
        }

        DB::transaction(function () use ($user, $rows): void {
            $user->permissionOverrides()->delete();

            foreach ($rows as $row) {
                UserPermission::create($row);
            }
        });

        return redirect()->route('permissions', ['user_id' => $user->id])
            ->with('status', 'Permissions updated for '.$user->name.'.');
    }

    /**
     * Flatten config/permissions.php into section → rows for the matrix.
     * role_default reflects the given role's baseline grant.
     *
     * @return array<int, array{section: string, permissions: array<int, array{key: string, label: string, role_default: bool}>}>
     */
    private function permissionTree(?int $role = null): array
    {
        $labels = (array) config('permissions.permissions');
        $sections = [];

        foreach ($labels as $key => $label) {
            $parts = explode('.', (string) $key);

            if (count($parts) === 1) {
                continue; // parent section headers
            }

            $roleDefault = false;

            if ($role !== null) {
                foreach ((array) config('permissions.roles.'.$role, []) as $grant) {
                    $grant = rtrim((string) $grant, '.*');

                    if ($key === $grant || str_starts_with((string) $key, $grant.'.')) {
                        $roleDefault = true;
                        break;
                    }
                }
            }

            $sections[$parts[0]][] = [
                'key' => (string) $key,
                'label' => $label,
                'role_default' => $roleDefault,
            ];
        }

        $grouped = [];

        foreach ($sections as $top => $permissions) {
            $grouped[] = [
                'section' => $labels[$top] ?? 'Permission '.$top,
                'permissions' => $permissions,
            ];
        }

        return $grouped;
    }
}
