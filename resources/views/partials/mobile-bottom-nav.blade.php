<nav class="mobile-bottom-nav d-lg-none" aria-label="Mobile navigation">
    <a href="{{ route('dashboard') }}" class="nav-item @if(Route::is('dashboard')) active @endif" aria-current="@if(Route::is('dashboard')) page @endif">
        <i class="bi bi-grid-1x2" aria-hidden="true"></i>
        <span>Home</span>
    </a>
    @if(auth()->user()?->hasPermission('4.9'))
        <button type="button" class="nav-item @if(Route::is('tasks.my*') || Route::is('tasks')) active @endif" id="btn-tasks-popover" aria-label="Tasks" aria-haspopup="menu" aria-expanded="false">
            <i class="bi bi-clipboard-check" aria-hidden="true"></i>
            <span>Tasks</span>
        </button>
        <div class="tasks-magic-popover" id="tasks-magic-popover" role="menu" aria-label="Tasks">
            <a href="{{ route('tasks.my') }}" class="tpop-item @if(Route::is('tasks.my*')) active @endif" role="menuitem">
                <i class="bi bi-person-check" aria-hidden="true"></i>My tasks
            </a>
            <a href="{{ route('tasks') }}" class="tpop-item @if(Route::is('tasks') && !Route::is('tasks.my*')) active @endif" role="menuitem">
                <i class="bi bi-clipboard-check" aria-hidden="true"></i>Task list
            </a>
        </div>
    @else
        <a href="{{ route('tasks.my') }}" class="nav-item @if(Route::is('tasks.my*')) active @endif" aria-current="@if(Route::is('tasks.my*')) page @endif">
            <i class="bi bi-clipboard-check" aria-hidden="true"></i>
            <span>Tasks</span>
        </a>
    @endif
    <button type="button" class="nav-item fab-item" id="btn-quick-fab" aria-label="Quick actions" aria-haspopup="dialog">
        <span class="fab-circle"><i class="bi bi-lightning-charge" aria-hidden="true"></i></span>
    </button>
    @if($unreadCount = cache()->remember('unread-'.auth()->id(), 60, fn () => \App\Models\Notification::where('user_id', auth()->id())->unread()->count()))
        <a href="{{ route('notifications') }}" class="nav-item @if(Route::is('notifications*')) active @endif" aria-current="@if(Route::is('notifications*')) page @endif">
            <span class="position-relative">
                <i class="bi bi-bell" aria-hidden="true"></i>
                <span class="badge rounded-pill bg-danger nav-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
            </span>
            <span>Alerts</span>
        </a>
    @else
        <a href="{{ route('notifications') }}" class="nav-item @if(Route::is('notifications*')) active @endif" aria-current="@if(Route::is('notifications*')) page @endif">
            <i class="bi bi-bell" aria-hidden="true"></i>
            <span>Alerts</span>
        </a>
    @endif
    <button type="button" class="nav-item" id="btn-mobile-menu" aria-label="Open menu">
        <i class="bi bi-list" aria-hidden="true"></i>
        <span>Menu</span>
    </button>
</nav>

<div class="mobile-sheet" id="quick-sheet" role="dialog" aria-label="Quick actions">
    <div class="sheet-handle" aria-hidden="true"></div>
    <div class="sheet-title mono text-muted px-3 pt-2 pb-1">Quick actions</div>
    @if(auth()->user()?->hasPermission('4.2'))
        <a href="{{ route('tasks.create') }}" class="sheet-item">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>New task
        </a>
    @endif
    @if(auth()->user()?->hasPermission('5.1'))
        <a href="{{ route('attendance') }}" class="sheet-item">
            <i class="bi bi-clock-history" aria-hidden="true"></i>Clock in / out
        </a>
    @endif
    @if(auth()->user()?->hasPermission('8.2'))
        <a href="{{ route('incidents.create') }}" class="sheet-item">
            <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>Report incident
        </a>
    @endif
    <a href="{{ route('notifications') }}" class="sheet-item">
        <i class="bi bi-bell" aria-hidden="true"></i>Notifications
    </a>
    <div class="px-3 py-3 d-grid">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-touch w-100">
                <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Logout
            </button>
        </form>
    </div>
</div>
