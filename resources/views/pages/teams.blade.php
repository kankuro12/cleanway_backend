@extends('layouts.app')

@section('title', 'Teams')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">People · Structure</span>
            <h1 class="h3 mt-1 mb-0">Teams</h1>
        </div>
        <a href="{{ route('personnel') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to personnel
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card shadow-sm mb-4 reveal" style="--d: 80ms">
        <div class="card-header"><i class="bi bi-plus-lg me-2" aria-hidden="true"></i>Add team</div>
        <div class="card-body">
            <form method="POST" action="{{ route('teams.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3">
                    <label for="name" class="visually-hidden">Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Team name" required>
                </div>
                <div class="col-md-3">
                    <label for="branch_id" class="visually-hidden">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-select">
                        <option value="">Branch</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lead_id" class="visually-hidden">Team lead</label>
                    <select name="lead_id" id="lead_id" class="form-select">
                        <option value="">Team lead</option>
                        @foreach ($leads as $lead)
                            <option value="{{ $lead->id }}">{{ $lead->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    @foreach ($teams as $team)
        <div class="card shadow-sm mb-3 reveal" style="--d: 120ms">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h6 mb-0">
                        <i class="bi bi-person-workspace me-2" style="color: var(--cw-accent)" aria-hidden="true"></i>
                        {{ $team->name }}
                        <small class="text-muted fw-normal">· {{ $team->branch?->name ?? 'No branch' }} · Lead: {{ $team->lead?->name ?? '—' }}</small>
                    </h2>
                    <span class="status-badge status-muted">{{ $team->members_count }} members</span>
                </div>

                <div class="mt-3">
                    <form method="POST" action="{{ route('teams.members.store', $team) }}" class="row g-2">
                        @csrf
                        <div class="col-md-5">
                            <label for="user_id" class="visually-hidden">Member</label>
                            <select name="user_id" id="user_id" class="form-select form-select-sm" required>
                                <option value="">Add member…</option>
                                @foreach ($leads->merge(\App\Models\User::where('role', \App\Models\User::ROLE_CLEANER)->orderBy('name')->get(['id', 'name'])) as $candidate)
                                    @continue($team->members->contains('id', $candidate->id))
                                    <option value="{{ $candidate->id }}">{{ $candidate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="role_in_team" class="visually-hidden">Role in team</label>
                            <input type="text" name="role_in_team" id="role_in_team" class="form-control form-control-sm" placeholder="Role in team (optional)">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Add
                            </button>
                        </div>
                    </form>
                </div>

                @if ($team->members->isNotEmpty())
                    <ul class="list-group mt-3">
                        @foreach ($team->members as $member)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="bi bi-person me-2" style="color: var(--cw-faint)" aria-hidden="true"></i>
                                    {{ $member->name }}
                                    @if ($member->pivot->role_in_team)
                                        <span class="status-badge status-muted ms-2">{{ $member->pivot->role_in_team }}</span>
                                    @endif
                                </span>
                                <form method="POST" action="{{ route('teams.members.destroy', [$team, $member]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-lg me-1" aria-hidden="true"></i>Remove
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mt-3 mb-0"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>No members yet.</p>
                @endif
            </div>
        </div>
    @endforeach

    @if ($teams->isEmpty())
        <div class="card shadow-sm reveal" style="--d: 160ms">
            <div class="empty-state">
                <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-person-workspace"></i></span>
                No teams yet. Create one above.
            </div>
        </div>
    @endif
@endsection
