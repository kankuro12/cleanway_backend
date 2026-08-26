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
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;600;700;800;900&family=IBM+Plex+Mono:wght@400;500;600;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="{{ versioned_asset('css/tokens.css') }}" rel="stylesheet">
    <link href="{{ versioned_asset('css/components.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <!-- Modern Offcanvas Mobile Drawer / Desktop Sidebar -->
        <!-- Modern Offcanvas Mobile Drawer / Desktop Sidebar (Matching Breezeway Operations UI) -->
        <nav class="admin-sidebar" id="app-sidebar" aria-label="Main navigation">
            <div class="sidebar-brand d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                <div>
                    <span class="sidebar-brand-name">cleanway</span>
                    <span class="sidebar-brand-tag">operations</span>
                </div>
                <!-- Native Mobile Drawer Close Button -->
                <button type="button" class="btn-close d-lg-none" id="sidebar-close" aria-label="Close navigation"></button>
            </div>

            <ul class="sidebar-nav py-2 px-0">
                @php
                    $isCleaner = auth()->user()?->hasRole(\App\Models\User::ROLE_CLEANER);
                @endphp

                <!-- 1. SECTION 1: SUPERVISOR & ADMIN CORE (Only shown to Supervisor / Admin) -->
                @if(! $isCleaner)
                    <li>
                        <a href="{{ route('dashboard') }}" class="sidebar-link @if(Route::is('dashboard')) active @endif">
                            <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
                        </a>
                    </li>
                    @if(auth()->user()?->hasPermission('4.9') || auth()->user()?->hasRole(0) || auth()->user()?->hasRole(1))
                        <li>
                            <a href="{{ route('tasks') }}" class="sidebar-link @if(Route::is('tasks') && !Route::is('tasks.my*', 'tasks.worksheet')) active @endif">
                                <i class="bi bi-check2-square" aria-hidden="true"></i> All tasks
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->hasPermission('3.1'))
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
                @endif

                <!-- 2. SECTION 2: PERSONAL / FIELD WORK (Shown to ALL) -->
                <li class="sidebar-section">My Work</li>
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

                <!-- 3. SECTION 3: REPORTS (Supervisor / Admin) -->
                @if(! $isCleaner)
                    <li class="sidebar-section">Reports</li>
                    @if(auth()->user()?->hasPermission('4.9') || auth()->user()?->hasRole(0) || auth()->user()?->hasRole(1))
                        <li>
                            <a href="{{ route('tasks.worksheet') }}" class="sidebar-link @if(Route::is('tasks.worksheet')) active @endif">
                                <i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Work Sheet
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->hasPermission('7.1'))
                        <li>
                            <a href="{{ route('reports') }}" class="sidebar-link @if(Route::is('reports') && !Route::is('reports.shifts')) active @endif">
                                <i class="bi bi-bar-chart" aria-hidden="true"></i> Reports
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.shifts') }}" class="sidebar-link @if(Route::is('reports.shifts')) active @endif">
                                <i class="bi bi-calendar2-range" aria-hidden="true"></i> Shifts Report
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reports.payouts') }}" class="sidebar-link @if(Route::is('reports.payouts')) active @endif">
                                <i class="bi bi-wallet2" aria-hidden="true"></i> Payout Sheet
                            </a>
                        </li>
                    @endif
                @endif

                <!-- 4. SECTION 4: OPERATIONS & MANAGEMENT (Supervisor / Admin - other than setup) -->
                @if(! $isCleaner)
                    <li class="sidebar-section">Operations</li>
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
                            <a href="{{ route('attendance') }}" class="sidebar-link @if(Route::is('attendance') && !Route::is('attendance.corrections')) active @endif">
                                <i class="bi bi-clock" aria-hidden="true"></i> Attendance
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->hasPermission('6.1'))
                        <li>
                            <a href="{{ route('attendance.corrections') }}" class="sidebar-link @if(Route::is('attendance.corrections*')) active @endif">
                                <i class="bi bi-clipboard-check" aria-hidden="true"></i> Attendance Corrections
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
                    @if(auth()->user()?->hasPermission('2.1'))
                        <li>
                            <a href="{{ route('personnel') }}" class="sidebar-link @if(Route::is('personnel*')) active @endif">
                                <i class="bi bi-person-badge" aria-hidden="true"></i> Personnel
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('teams') }}" class="sidebar-link @if(Route::is('teams*')) active @endif">
                                <i class="bi bi-people-fill" aria-hidden="true"></i> Teams
                            </a>
                        </li>
                    @endif
                @endif

                <!-- 5. SECTION 5: SETUP & MASTER DATA (Supervisor / Admin / Super Admin) -->
                @if(! $isCleaner)
                    <li class="sidebar-section">Setup</li>
                    @if(auth()->user()?->hasPermission('2') || auth()->user()?->hasRole(0))
                        <li>
                            <a href="{{ route('branches') }}" class="sidebar-link @if(Route::is('branches*')) active @endif">
                                <i class="bi bi-building" aria-hidden="true"></i> Branches
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->hasPermission('3.1') || auth()->user()?->hasRole(0) || auth()->user()?->hasRole(1))
                        <li>
                            <a href="{{ route('property-categories') }}" class="sidebar-link @if(Route::is('property-categories*')) active @endif">
                                <i class="bi bi-grid" aria-hidden="true"></i> Property Categories
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('property-tags') }}" class="sidebar-link @if(Route::is('property-tags*')) active @endif">
                                <i class="bi bi-tags" aria-hidden="true"></i> Property Tags
                            </a>
                        </li>
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
                    @if(auth()->user()?->hasPermission('4.1') || auth()->user()?->hasRole(0) || auth()->user()?->hasRole(1))
                        <li>
                            <a href="{{ route('task-types') }}" class="sidebar-link @if(Route::is('task-types*')) active @endif">
                                <i class="bi bi-list-check" aria-hidden="true"></i> Task Types
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('checklists') }}" class="sidebar-link @if(Route::is('checklists*')) active @endif">
                                <i class="bi bi-card-checklist" aria-hidden="true"></i> Checklists
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('recurrences') }}" class="sidebar-link @if(Route::is('recurrences*')) active @endif">
                                <i class="bi bi-arrow-repeat" aria-hidden="true"></i> Recurrences
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->hasPermission('1') || auth()->user()?->hasRole(0))
                        <li>
                            <a href="{{ route('settings') }}" class="sidebar-link @if(Route::is('settings*') && !Route::is('permissions*')) active @endif">
                                <i class="bi bi-gear" aria-hidden="true"></i> Settings
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('permissions') }}" class="sidebar-link @if(Route::is('permissions*')) active @endif">
                                <i class="bi bi-shield-lock" aria-hidden="true"></i> Permissions
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->hasPermission('9.1') || auth()->user()?->hasRole(0))
                        <li>
                            <a href="{{ route('audit') }}" class="sidebar-link @if(Route::is('audit*')) active @endif">
                                <i class="bi bi-journal-text" aria-hidden="true"></i> Audit Logs
                            </a>
                        </li>
                    @endif
                @endif
            </ul>

            <!-- Sidebar Footer — logout only -->
            <div class="sidebar-user-footer p-3 border-top mt-auto">
                <form method="POST" action="{{ route('logout') }}" class="d-grid">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm" aria-label="Log out">
                        <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Log out
                    </button>
                </form>
            </div>
        </nav>

        <div class="sidebar-backdrop" id="sidebar-backdrop" aria-hidden="true"></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left d-flex align-items-center min-w-0 flex-shrink-1">
                    <button type="button" class="btn btn-icon-touch d-lg-none me-2 p-0 border-0 bg-transparent flex-shrink-0" id="btn-mobile-menu" aria-label="Open navigation menu" style="min-width: 36px; min-height: 36px;">
                        <i class="bi bi-list fs-3 text-dark"></i>
                    </button>
                    <div class="topbar-title" aria-label="Current section" title="@yield('title', 'Dashboard')">@yield('title', 'Dashboard')</div>
                </div>
                <div class="topbar-user flex-shrink-0">
                    <!-- Global Date Filter Link -->
                    <a href="{{ route('tasks', ['tab' => 'filters']) }}" class="prop-date-chip" title="Filter & Schedule">
                        <i class="bi bi-calendar-event"></i>
                        <span>{{ today()->format('M j') }}</span>
                        <i class="bi bi-funnel text-muted" style="font-size: 11px;"></i>
                    </a>

                    <!-- Global Search Link -->
                    <a href="{{ route('search') }}" class="prop-icon-btn" title="Global Search" aria-label="Search">
                        <i class="bi bi-search"></i>
                    </a>

                    @yield('topbar_actions')
                    <span class="topbar-clock d-none d-md-inline-block" data-clock></span>
                    <a href="{{ route('notifications') }}" class="position-relative topbar-bell" aria-label="Notifications">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        @if($unreadCount = cache()->remember('unread-'.auth()->id(), 60, fn () => \App\Models\Notification::where('user_id', auth()->id())->unread()->count()))
                            <span class="badge rounded-pill bg-danger nav-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                        @endif
                    </a>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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

            // Modal URL Hash & Browser Back Button Auto-Dismissal
            $(document).on('show.bs.modal', '.modal', function () {
                var modalId = $(this).attr('id') || 'modal';
                var customHash = $(this).data('custom-hash') || ('#' + modalId);
                if (!window.location.hash || window.location.hash === '#') {
                    if (window.history && window.history.pushState) {
                        window.history.pushState({ modalId: modalId }, '', customHash);
                    }
                }
            });

            $(document).on('hidden.bs.modal', '.modal', function () {
                $(this).removeData('custom-hash');
                if (window.location.hash && window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname + window.location.search);
                }
            });

            window.addEventListener('popstate', function () {
                var $openModals = $('.modal.show');
                if ($openModals.length > 0) {
                    $openModals.each(function () {
                        var modalInstance = bootstrap.Modal.getInstance(this);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    });
                }
            });
        })(jQuery);
    </script>
    <script src="{{ versioned_asset('js/filters.js') }}"></script>
    @stack('scripts')
    @include('partials.firebase')
</body>
</html>
