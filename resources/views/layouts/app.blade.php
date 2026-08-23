<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — CleanWay Ops</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('logo.jpg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="{{ asset('css/tokens.css') }}" rel="stylesheet">
    <link href="{{ asset('css/components.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <!-- Modern Offcanvas Mobile Drawer / Desktop Sidebar -->
        <!-- Modern Offcanvas Mobile Drawer / Desktop Sidebar (Matching Breezeway Operations UI) -->
        <nav class="admin-sidebar" id="app-sidebar" aria-label="Main navigation">
            <div class="sidebar-brand d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <div>
                    <span class="sidebar-brand-name d-block" style="color: #0284c7; font-family: var(--cw-font-display); font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.1;">cleanway</span>
                    <span class="sidebar-brand-tag d-block" style="color: #64748b; font-family: var(--cw-font-mono); font-size: 0.75rem; font-weight: 500; letter-spacing: 0.08em;">operations</span>
                </div>
                <!-- Native Mobile Drawer Close Button -->
                <button type="button" class="btn-close d-lg-none" id="sidebar-close" aria-label="Close navigation"></button>
            </div>

            <ul class="sidebar-nav py-2 px-0">
                <!-- 1. CORE BREEZEWAY NAVIGATION (Screenshot Match) -->
                <li>
                    <a href="{{ route('dashboard') }}" class="sidebar-link @if(Route::is('dashboard')) active @endif">
                        <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
                    </a>
                </li>
                @if(auth()->user()?->hasPermission('4.9'))
                    <li>
                        <a href="{{ route('tasks') }}" class="sidebar-link @if(Route::is('tasks') && !Route::is('tasks.my*', 'tasks.worksheet')) active @endif">
                            <i class="bi bi-check2-square" aria-hidden="true"></i> All tasks
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tasks.worksheet') }}" class="sidebar-link @if(Route::is('tasks.worksheet')) active @endif">
                            <i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Work Sheet
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('3.1') && ! auth()->user()?->hasRole(2))
                    <li>
                        <a href="{{ route('properties') }}" class="sidebar-link @if(Route::is('properties*') && !Route::is('properties.create', 'properties.edit')) active @endif">
                            <i class="bi bi-house-door" aria-hidden="true"></i> Properties
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('clients') }}" class="sidebar-link @if(Route::is('clients*')) active @endif">
                            <i class="bi bi-people" aria-hidden="true"></i> Clients
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('4.1'))
                    <li>
                        <a href="{{ route('tasks.my') }}" class="sidebar-link @if(Route::is('tasks.my*') && request()->get('tab') !== 'history') active @endif">
                            <i class="bi bi-text-indent-left" aria-hidden="true"></i> My tasks
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('tasks.my', ['tab' => 'history']) }}" class="sidebar-link @if(Route::is('tasks.my*') && request()->get('tab') === 'history') active @endif">
                            <i class="bi bi-clock-history" aria-hidden="true"></i> My history
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payroll.index') }}" class="sidebar-link @if(Route::is('payroll*')) active @endif">
                            <i class="bi bi-wallet2" aria-hidden="true"></i> Payroll & Earnings
                        </a>
                    </li>
                @endif

                <!-- 2. FIELD & OPERATIONS TOOLS -->
                <li class="sidebar-section px-4 pt-3 pb-1 text-uppercase text-muted extra-small fw-bold">Operations & Field</li>
                @if(auth()->user()?->hasPermission('4.1'))
                    <li>
                        <a href="{{ route('calendar') }}" class="sidebar-link @if(Route::is('calendar*')) active @endif">
                            <i class="bi bi-calendar3" aria-hidden="true"></i> Calendar
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('5.1'))
                    <li>
                        <a href="{{ route('shifts') }}" class="sidebar-link @if(Route::is('shifts*')) active @endif">
                            <i class="bi bi-calendar-range" aria-hidden="true"></i> Shifts
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('attendance') }}" class="sidebar-link @if(Route::is('attendance*')) active @endif">
                            <i class="bi bi-clock" aria-hidden="true"></i> Attendance
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('4.5'))
                    <li>
                        <a href="{{ route('approvals') }}" class="sidebar-link @if(Route::is('approvals*')) active @endif">
                            <i class="bi bi-check2-circle" aria-hidden="true"></i> Approvals
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('8.1'))
                    <li>
                        <a href="{{ route('incidents') }}" class="sidebar-link @if(Route::is('incidents*')) active @endif">
                            <i class="bi bi-exclamation-octagon" aria-hidden="true"></i> Incidents
                        </a>
                    </li>
                @endif

                <!-- 3. SYSTEM & MANAGEMENT -->
                <li class="sidebar-section px-4 pt-3 pb-1 text-uppercase text-muted extra-small fw-bold">System</li>
                @if(auth()->user()?->hasPermission('2.1'))
                    <li>
                        <a href="{{ route('personnel') }}" class="sidebar-link @if(Route::is('personnel*')) active @endif">
                            <i class="bi bi-people" aria-hidden="true"></i> Personnel
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teams') }}" class="sidebar-link @if(Route::is('teams*')) active @endif">
                            <i class="bi bi-person-workspace" aria-hidden="true"></i> Teams
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('7.1'))
                    <li>
                        <a href="{{ route('reports') }}" class="sidebar-link @if(Route::is('reports*')) active @endif">
                            <i class="bi bi-bar-chart" aria-hidden="true"></i> Reports
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('4.8') || auth()->user()?->hasRole(1))
                    <li>
                        <a href="{{ route('checklists') }}" class="sidebar-link @if(Route::is('checklists*')) active @endif">
                            <i class="bi bi-card-checklist" aria-hidden="true"></i> Checklists
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('3.1') || auth()->user()?->hasRole(1))
                    <li>
                        <a href="{{ route('bed-types') }}" class="sidebar-link @if(Route::is('bed-types*')) active @endif">
                            <i class="bi bi-layout-sidebar" aria-hidden="true"></i> Bed Types
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('linen-types') }}" class="sidebar-link @if(Route::is('linen-types*')) active @endif">
                            <i class="bi bi-tag" aria-hidden="true"></i> Linen Types
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->hasPermission('1'))
                    <li>
                        <a href="{{ route('settings') }}" class="sidebar-link @if(Route::is('settings*')) active @endif">
                            <i class="bi bi-gear" aria-hidden="true"></i> Settings
                        </a>
                    </li>
                @endif
            </ul>

            <!-- Sidebar User Profile Footer (Matching Breezeway Operations Screenshot) -->
            <div class="sidebar-user-footer p-3 border-top mt-auto d-flex align-items-center gap-2">
                @php
                    $uName = auth()->user()?->name ?? 'Nishabh Karki';
                    $nameParts = explode(' ', trim($uName));
                    $initials = strtoupper(substr($nameParts[0] ?? 'N', 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                @endphp
                <div class="avatar-circle-chip d-grid place-items-center rounded-circle fw-bold" style="width:36px; height:36px; background-color:#cbd5e1; color:#334155; font-size:13px; flex-shrink:0;">
                    {{ $initials ?: 'NK' }}
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-dark text-truncate small">{{ $uName }}</div>
                </div>
            </div>
        </nav>

        <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <h1 class="topbar-title">@yield('title', 'Dashboard')</h1>
                <div class="topbar-user">
                    <span class="topbar-clock" data-clock></span>
                    <a href="{{ route('notifications') }}" class="position-relative topbar-bell" aria-label="Notifications">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        @if($unreadCount = cache()->remember('unread-'.auth()->id(), 60, fn () => \App\Models\Notification::where('user_id', auth()->id())->unread()->count()))
                            <span class="badge rounded-pill bg-danger nav-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
                    <span class="user-chip">
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                        {{ auth()->user()?->name }}
                    </span>
                    <span class="role-chip">@switch(auth()->user()?->role)
@case(0) Admin @break
@case(1) Supervisor @break
@default Cleaner @endswitch</span>
                    @if(auth()->user()?->hasPermission('4.2'))
                        <a href="{{ route('tasks.create') }}" class="btn btn-primary btn-sm d-none d-lg-inline-flex">
                            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Add task
                        </a>
                    @endif
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

    @include('partials.mobile-bottom-nav')

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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

            function openSidebar() {
                $('#app-sidebar').addClass('open');
                $('#sidebar-backdrop').addClass('show');
            }

            function closeSidebar() {
                $('#app-sidebar').removeClass('open');
                $('#sidebar-backdrop').removeClass('show');
            }

            $('#btn-mobile-menu').on('click', openSidebar);
            $('#sidebar-close, #sidebar-backdrop').on('click', closeSidebar);

            // Auto-close sidebar on mobile when navigating links
            $('.sidebar-link').on('click', function () {
                if (window.innerWidth < 992) {
                    closeSidebar();
                }
            });

            $('#btn-quick-fab').on('click', function (e) {
                e.stopPropagation();
                $('#quick-sheet').toggleClass('open');
            });
            $('#quick-sheet').on('click', '.sheet-handle, .sheet-item', function () {
                $('#quick-sheet').removeClass('open');
            });
            $(document).on('click', function (e) {
                if ($('#quick-sheet').hasClass('open') && !$(e.target).closest('#quick-sheet, #btn-quick-fab').length) {
                    $('#quick-sheet').removeClass('open');
                }
                if ($('#tasks-magic-popover').hasClass('open') && !$(e.target).closest('#btn-tasks-popover, #tasks-magic-popover').length) {
                    $('#tasks-magic-popover').removeClass('open');
                    $('#btn-tasks-popover').attr('aria-expanded', 'false');
                }
            });
            $('#btn-tasks-popover').on('click', function (e) {
                e.stopPropagation();
                var open = $('#tasks-magic-popover').toggleClass('open').hasClass('open');
                $(this).attr('aria-expanded', open ? 'true' : 'false');
            });
            $('#tasks-magic-popover').on('click', '.tpop-item', function () {
                $('#tasks-magic-popover').removeClass('open');
                $('#btn-tasks-popover').attr('aria-expanded', 'false');
            });
        })(jQuery);
    </script>
    <script src="{{ asset('js/filters.js') }}"></script>
    @stack('scripts')
    @include('partials.firebase')
</body>
</html>
