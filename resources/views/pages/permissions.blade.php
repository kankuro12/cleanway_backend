@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">Settings · Roles & Permissions</span>
            <h1 class="h3 mt-1 mb-0">Permission fine-tuning</h1>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger py-2 reveal" role="alert">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="row g-2 mb-3 reveal" style="--d: 80ms">
        <div class="col-md-4">
            <label for="user_id" class="form-label">Personnel</label>
            <select name="user_id" id="user_id" class="form-select" onchange="this.form.submit()">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected($selected && $selected->id === $user->id)>
                        {{ $user->name }} ({{ $user->email }}) · R{{ $user->role }} · {{ $user->status }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-8 d-flex align-items-end">
            <span class="text-muted small">
                Overrides sit on top of the role baseline. A parent key (e.g. <code>3</code>) covers its children (e.g. <code>3.1</code>).
            </span>
        </div>
    </form>

    @if($selected)
        <form method="POST" action="{{ route('permissions.update', $selected) }}" class="reveal" style="--d: 120ms">
            @csrf
            @foreach ($permissionTree as $group)
                <div class="card shadow-sm mb-3">
                    <div class="card-header mono">{{ $group['section'] }}</div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Permission</th>
                                    <th>Role default</th>
                                    <th style="width: 220px;">Override</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($group['permissions'] as $perm)
                                    @php
                                        $override = $overrides->get($perm['key']);
                                        $value = $override ? ($override->granted ? 'grant' : 'deny') : '';
                                    @endphp
                                    <tr>
                                        <td class="mono small">{{ $perm['key'] }}</td>
                                        <td class="small">{{ $perm['label'] }}</td>
                                        <td>
                                            <span class="status-badge status-{{ $perm['role_default'] ? 'active' : 'muted' }}">
                                                {{ $perm['role_default'] ? 'granted by role' : 'not granted' }}
                                            </span>
                                        </td>
                                        <td>
                                            <select name="permissions[{{ $perm['key'] }}]" class="form-select form-select-sm">
                                                <option value="">Default (role)</option>
                                                <option value="grant" @selected($value === 'grant')>Grant</option>
                                                <option value="deny" @selected($value === 'deny')>Deny</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <button class="btn btn-primary">
                <i class="bi bi-check2 me-1" aria-hidden="true"></i>Save permissions for {{ $selected->name }}
            </button>
        </form>
    @endif
@endsection
