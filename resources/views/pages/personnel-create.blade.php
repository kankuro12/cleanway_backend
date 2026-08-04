@extends('layouts.app')

@section('title', 'New Person')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">People · New record</span>
            <h2 class="h3 mt-1 mb-0">Create personnel</h2>
        </div>
        <a href="{{ route('personnel') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('personnel.store') }}" class="card shadow-sm reveal" style="--d: 80ms">
        @csrf
        <div class="card-header"><i class="bi bi-person-badge me-2" aria-hidden="true"></i>Identity &amp; access</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="name" class="form-label">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">Role *</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="">—</option>
                        <option value="0">Admin</option>
                        <option value="1" @selected(old('role') === '1')>Supervisor</option>
                        <option value="2" @selected(old('role') === '2')>Cleaner</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status *</label>
                    <select name="status" id="status" class="form-select" required>
                        @foreach (['active', 'invited', 'inactive', 'suspended', 'on_leave', 'archived'] as $status)
                            <option value="{{ $status }}" @selected(old('status', 'active') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="employee_no" class="form-label">Employee no.</label>
                    <input type="text" id="employee_no" name="employee_no" value="{{ old('employee_no') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="employment_type" class="form-label">Employment type</label>
                    <input type="text" id="employment_type" name="employment_type" value="{{ old('employment_type') }}" class="form-control">
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
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="team_id" class="form-label">Team</label>
                    <select name="team_id" id="team_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id') == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="manager_id" class="form-label">Manager</label>
                    <select name="manager_id" id="manager_id" class="form-select">
                        <option value="">—</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected(old('manager_id') == $manager->id)>{{ $manager->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="start_date" class="form-label">Start date</label>
                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="end_date" class="form-label">End date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control">
                </div>
            </div>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('personnel') }}" class="btn btn-outline-secondary me-2">Cancel</a>
            <button class="btn btn-primary">
                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Save person
            </button>
        </div>
    </form>
@endsection
