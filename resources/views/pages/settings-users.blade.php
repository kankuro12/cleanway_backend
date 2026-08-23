@extends('layouts.app')

@section('title', 'Settings — Users')

@section('content')
    <div class="mb-4 reveal">
        <span class="eyebrow">System · Access</span>
        <h1 class="h3 mt-1 mb-0">Users</h1>
    </div>
    <div class="card shadow-sm reveal" style="--d: 80ms">
        <div class="empty-state">
            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-person-gear"></i></span>
            User settings land with the settings module.
        </div>
    </div>
@endsection
