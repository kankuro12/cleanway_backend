@extends('layouts.app')

@section('title', 'Edit Person')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">People · Record #{{ $user->id }}</span>
            <h2 class="h3 mt-1 mb-0">{{ $user->name }}</h2>
        </div>
        <a href="{{ route('personnel') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('personnel.update', $user) }}" class="card shadow-sm reveal" style="--d: 80ms">
        @csrf
        @method('PUT')
        <div class="card-header"><i class="bi bi-person-badge me-2" aria-hidden="true"></i>Identity &amp; access</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">Role *</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="0" @selected((int) $user->role === 0)>Admin</option>
                        <option value="1" @selected((int) $user->role === 1)>Supervisor</option>
                        <option value="2" @selected((int) $user->role === 2)>Cleaner</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        @foreach (['active', 'invited', 'inactive', 'suspended', 'on_leave', 'archived'] as $status)
                            <option value="{{ $status }}" @selected($user->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="employee_no" class="form-label">Employee no.</label>
                    <input type="text" id="employee_no" name="employee_no" value="{{ old('employee_no', $user->employee_no) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="employment_type" class="form-label">Employment type</label>
                    <input type="text" id="employment_type" name="employment_type" value="{{ old('employment_type', $user->employment_type) }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card-header"><i class="bi bi-diagram-3 me-2" aria-hidden="true"></i>Placement</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="branch_id" class="form-label">Branch</label>
                    <select name="branch_id" id="branch_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($user->branch_id === $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="team_id" class="form-label">Team</label>
                    <select name="team_id" id="team_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected($user->team_id === $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="manager_id" class="form-label">Manager</label>
                    <select name="manager_id" id="manager_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected($user->manager_id === $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $user->start_date?->toDateString()) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $user->end_date?->toDateString()) }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <form method="POST" action="{{ route('personnel.destroy', $user) }}" onsubmit="return confirm('Archive this person? This removes them from active rosters.')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-archive me-1" aria-hidden="true"></i>Archive
                </button>
            </form>
            <button class="btn btn-primary">
                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Save changes
            </button>
        </div>
    </form>
@endsection
