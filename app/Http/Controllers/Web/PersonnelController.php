<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Models\Branch;
use App\Models\Team;
use App\Models\User;
use App\Support\PersonnelScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersonnelController extends Controller
{
    public function index(Request $request): View
    {
        $users = PersonnelScope::apply(User::query(), $request->user())
            ->with(['branch:id,name', 'team:id,name', 'manager:id,name'])
            ->filter($request->all())
            ->orderBy('name')
            ->paginate(25);

        return view('pages.personnel', [
            'users' => $users,
            'roles' => [0 => 'Admin', 1 => 'Supervisor', 2 => 'Cleaner'],
        ]);
    }

    public function create(): View
    {
        return view('pages.personnel-create', [
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
            'managers' => User::where('role', User::ROLE_SUPERVISOR)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePersonnelRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            User::create($request->validated());
        });

        return redirect()->route('personnel')->with('status', 'Personnel created.');
    }

    public function edit(User $user): View
    {
        return view('pages.personnel-edit', [
            'user' => $user,
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
            'managers' => User::where('role', User::ROLE_SUPERVISOR)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePersonnelRequest $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user): void {
            $user->update($request->validated());
        });

        return redirect()->route('personnel')->with('status', 'Personnel updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        DB::transaction(function () use ($user): void {
            $user->update(['status' => User::STATUS_ARCHIVED]);
            $user->delete();
        });

        return redirect()->route('personnel')->with('status', 'Personnel archived.');
    }
}
