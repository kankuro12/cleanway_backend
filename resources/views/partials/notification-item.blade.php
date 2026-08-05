<div class="list-group-item d-flex justify-content-between align-items-start {{ $notification->read_at ? 'opacity-75' : '' }}">
    <div class="me-3">
        <div class="fw-semibold small">
            @if(!$notification->read_at)<span class="status-badge status-warning me-1">new</span>@endif
            {{ $notification->title }}
        </div>
        @if($notification->body)<div class="text-muted small">{{ $notification->body }}</div>@endif
        <small class="text-muted mono">{{ $notification->type }} · {{ $notification->read_at ? 'read '.$notification->read_at->diffForHumans() : $notification->created_at?->diffForHumans() }}</small>
    </div>
    @if(!$notification->read_at)
        <form method="POST" action="{{ route('notifications.read', $notification) }}" data-ajax>
            @csrf
            <button class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-check2 me-1" aria-hidden="true"></i>Read
            </button>
        </form>
    @endif
</div>
