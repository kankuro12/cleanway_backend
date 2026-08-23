@extends('layouts.app')

@section('title', 'Approval Queue')

@section('content')
    <div class="mb-4 reveal">
        <span class="eyebrow">Operations · Review</span>
        <h1 class="h3 mt-1 mb-0">Approval queue</h1>
    </div>
    <div class="card shadow-sm reveal" style="--d: 80ms">
        <div class="empty-state">
            <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
            Approval queue lands with the tasks module.
        </div>
    </div>
@endsection
