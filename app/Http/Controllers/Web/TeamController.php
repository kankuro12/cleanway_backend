<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('pages.teams', [
            'teams' => Team::withCount('members')->with(['branch:id,name', 'lead:id,name'])->orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'leads' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_ADMIN])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'lead_id' => ['nullable', 'exists:users,id'],
        ]);

        DB::transaction(fn () => Team::create($data));

        return redirect()->route('teams')->with('status', 'Team created.');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'lead_id' => ['nullable', 'exists:users,id'],
        ]);

        DB::transaction(fn () => $team->update($data));

        return redirect()->route('teams')->with('status', 'Team updated.');
    }

    public function addMember(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_in_team' => ['nullable', 'string', 'max:30'],
        ]);

        DB::transaction(function () use ($team, $data): void {
            TeamMember::updateOrCreate(
                ['team_id' => $team->id, 'user_id' => $data['user_id']],
                ['role_in_team' => $data['role_in_team'] ?? null, 'joined_at' => now(), 'left_at' => null],
            );
        });

        return redirect()->route('teams')->with('status', 'Member added.');
    }

    public function removeMember(Team $team, User $user): RedirectResponse
    {
        DB::transaction(function () use ($team, $user): void {
            TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->delete();
        });

        return redirect()->route('teams')->with('status', 'Member removed.');
    }
}
