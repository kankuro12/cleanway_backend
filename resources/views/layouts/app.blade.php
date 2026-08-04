<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — CleanWay Ops</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <nav class="admin-sidebar" id="app-sidebar" aria-label="Main navigation">
            <div class="sidebar-brand">
                <span class="sidebar-brand-mark" aria-hidden="true">
                    <i class="bi bi-droplet-half"></i>
                </span>
                <span>
                    <span class="sidebar-brand-name d-block">CLEANWAY</span>
                    <span class="sidebar-brand-tag">Field Operations</span>
                </span>
            </div>
            <div class="sidebar-hazard" aria-hidden="true"></div>

            <ul class="sidebar-nav">
                <li class="sidebar-section">Operations</li>
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-link @if(Route::is('dashboard')) active @endif">
                        <i class="bi bi-grid-1x2" aria-hidden="true"></i> Dashboard
                    </a>
                </li>
                @if(auth()->user()?->hasPermission('3.1'))
                    <li>
                        <a href="{{ route('properties') }}" class="sidebar-link @if(Route::is('properties*')) active @endif">
                            <i class="bi bi-building" aria-hidden="true"></i> Properties
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('4.1'))
                    <li>
                        <a href="{{ route('dashboard') }}" class="sidebar-link">
                            <i class="bi bi-clipboard-check" aria-hidden="true"></i> Tasks
                        </a>
                    </li>
                @endif

                <li class="sidebar-section">People</li>
                @if(auth()->user()?->hasPermission('2.1'))
                    <li>
                        <a href="{{ route('personnel') }}" class="sidebar-link @if(Route::is('personnel*')) active @endif">
                            <i class="bi bi-people" aria-hidden="true"></i> Personnel
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('branches') }}" class="sidebar-link @if(Route::is('branches*')) active @endif">
                            <i class="bi bi-diagram-3" aria-hidden="true"></i> Branches
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teams') }}" class="sidebar-link @if(Route::is('teams*')) active @endif">
                            <i class="bi bi-person-workspace" aria-hidden="true"></i> Teams
                        </a>
                    </li>
                @endif

                <li class="sidebar-section">Data</li>
                @if(auth()->user()?->hasPermission('7.1'))
                    <li>
                        <a href="{{ route('reports') }}" class="sidebar-link @if(Route::is('reports')) active @endif">
                            <i class="bi bi-bar-chart" aria-hidden="true"></i> Reports
                        </a>
                    </li>
                @endif

                <li class="sidebar-section">System</li>
                @if(auth()->user()?->hasPermission('1'))
                    <li>
                        <a href="{{ route('settings') }}" class="sidebar-link @if(Route::is('settings*')) active @endif">
                            <i class="bi bi-gear" aria-hidden="true"></i> Settings
                        </a>
                    </li>
                @endif
            </ul>

            <div class="sidebar-rail">
                ROL-{{ str_pad(auth()->id() ?? 0, 4, '0', STR_PAD_LEFT) }} · {{ auth()->user()?->role }}<br>
                UTC <span data-clock></span>
            </div>
        </nav>

        <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <button type="button" class="btn btn-outline-secondary btn-sm sidebar-toggle" id="sidebar-toggle" aria-label="Toggle navigation">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <h1 class="topbar-title">@yield('title', 'Dashboard')</h1>
                <div class="topbar-user">
                    <span class="topbar-clock d-none d-md-inline" data-clock></span>
                    <span class="user-chip">
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                        {{ auth()->user()?->name }}
                    </span>
                    <span class="role-chip">@switch(auth()->user()?->role)
@case(0) Admin @break
@case(1) Supervisor @break
@default Cleaner @endswitch</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="admin-content">
                @yield('content')
            </main>

            <footer class="admin-footer">
                CleanWay Ops · internal system · v0.1.0
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

        (function ($) {
            var tick = function () {
                $('[data-clock]').each(function () {
                    var el = $(this), h = new Date();
                    el.text(String(h.getUTCHours()).padStart(2, '0') + ':' + String(h.getUTCMinutes()).padStart(2, '0') + ':' + String(h.getUTCSeconds()).padStart(2, '0'));
                });
            };
            tick();
            setInterval(tick, 1000);

            $('#sidebar-toggle').on('click', function () {
                $('#app-sidebar').toggleClass('open');
                $('#sidebar-backdrop').toggleClass('show');
            });
            $('#sidebar-backdrop').on('click', function () {
                $('#app-sidebar').removeClass('open');
                $('#sidebar-backdrop').removeClass('show');
            });
        })(jQuery);
    </script>
    @stack('scripts')
</body>
</html>
