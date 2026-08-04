@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 reveal">
        <div>
            <span class="eyebrow">System · Notifications</span>
            <h2 class="h3 mt-1 mb-0">Inbox</h2>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-check2-all me-1" aria-hidden="true"></i>Mark all read
            </button>
        </form>
    </div>

    @if (session('status'))
        <div class="alert alert-success py-2 reveal" role="alert">{{ session('status') }}</div>
    @endif

    <div class="card shadow-sm reveal" style="--d: 100ms">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                <div class="list-group-item d-flex justify-content-between align-items-start {{ $notification->read_at ? 'opacity-75' : '' }}">
                    <div class="me-3">
                        <div class="fw-semibold small">
                            @if(!$notification->read_at)<span class="status-badge status-warning me-1">new</span>@endif
                            {{ $notification->title }}
                        </div>
                        @if($notification->body)<div class="text-muted small">{{ $notification->body }}</div>@endif
                        <small class="text-muted mono">{{ $notification->type }} · {{ $notification->created_at?->diffForHumans() }}</small>
                    </div>
                    @if(!$notification->read_at)
                        <form method="POST" action="{{ route('notifications.read', $notification) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-check2 me-1" aria-hidden="true"></i>Read
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="empty-state m-3">
                    <span class="empty-state-icon" aria-hidden="true"><i class="bi bi-bell"></i></span>
                    No notifications yet.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-3 reveal">{{ $notifications->links() }}</div>
@endsection
