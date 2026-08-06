# Mobile-First Redesign — Current

Spec: `work/mobile_first_dashboard_redesign.md`. Dashboard-only follow-up spec: `work/dashboard_redesign.md`. Filters/density plan: `work/mobile_filters_compact_lists_plan.md`.

## Mobile filters + compact lists (`work/mobile_filters_compact_lists_plan.md`) — done

- `partials/compact-filter-bar.blade.php` — single 48px row (<768px): search (with hidden copies of other active params) + Filters button with active-count badge + Clear link; active-filter pills strip (`#filter-pills`, JS-built from the form's own selected values, 1-tap remove = clear field + resubmit).
- Filter forms on 8 pages (tasks, properties, personnel, attendance, incidents, shifts, reports, audit) restructured: `id="filter-form"` + `filter-sheet-head`/`filter-sheet-foot` + fields wrapped in `row g-2 filter-sheet-body`. On <768px the single form node becomes a slide-up bottom sheet (CSS transform, no duplication); ≥768px unchanged inline form. `.filter-form { animation:none }` — `reveal` animation fill was overriding the sheet transform.
- `public/js/filters.js` (loaded in layout): toggle sheet, sync bar-search into sheet, build/remove pills, outside-tap close, aria-expanded.
- Magic tasks popover (bottom nav): permission `4.9` → Tasks tab becomes a button with spring-bounce popover (My Tasks / Task List, active states, blur backdrop, press-scale); no `4.9` → plain link to `tasks.my` (cleaners).
- Density: `.mobile-task-card.compact` (tighter padding/typography, 44px actions) applied to personnel/properties/attendance/shifts/approval-queue streams; `.table-cards` mobile padding tightened; incidents + branches tables converted to `table-cards` (incidents got `data-label` attrs); audit + reports got `d-lg-none` compact card streams (tables kept ≥lg).
- Not done (nothing to do): branches/teams have no filter forms; teams already card-based; tasks-cleaner has no filter form (tab switcher only).
- 153/153 tests green; all views compile (`view:cache`).

## Dashboard redesign (`work/dashboard_redesign.md`) — done

- Bento tap-to-filter: stat cards w/ `data-filter` attr (admin: Active tasks/Tasks today→all, Pending approval→submitted_for_approval,correction_requested; supervisor: Team tasks→all, Awaiting approval; cleaner: Tasks today→all, Completed→completed,approved). Mobile (<992px) tap = same-page feed filter + `.active` accent; desktop = normal link navigation. No-match → `#feed-empty` state. JS in `@push('scripts')`.
- Task feed cards: 4px status-tinted left border (`.mtc-b-assigned`=info blue, `.mtc-b-in_progress`=warning orange, `.mtc-b-completed`=success green, else muted), `data-status` for filtering, property name in meta, action strip (Start work [4.4] + View, 48px btn-touch).
- Attention queue: severity pills (incidents critical→danger/high→warning/else info; GPS→warning "gps"; corrections→info "pending") + 48px Review links (incidents→`incidents`, corrections→`attendance.corrections`; GPS has no detail page → no link).
- Verified 360/375/414/768 (mobile feed + nav) + 1200 (desktop table, no nav); no h-scroll; filter toggle on/off; cleaner + admin both tested (browser). 153/153.
- Note: `view:cache` left stale compile after edit — run `php84 artisan view:cache`/`view:clear` before browser-verifying blade changes.

## Done

- [x] Touch tokens (`--cw-touch-target-min`, bottom-nav/safe-area/z-index) in `tokens.css`.
- [x] Mobile components in `components.css`: `.mobile-bottom-nav`, `.mobile-sheet`, `.btn-touch`/`.btn-icon-touch`, `.bento-card`, `.hazard-bar`, `.mobile-task-card`, `.work-action-bar`, `.checklist-item`, body bottom padding for nav.
- [x] `partials/mobile-bottom-nav.blade.php` — 5 tabs (Home / Tasks / FAB / Shift[5.1] / Menu), included in `layouts.app` (d-lg-none). Menu opens existing sidebar drawer; FAB opens permission-gated quick-actions sheet (New task 4.2, Clock 5.1, Incident 8.2, Notifications, Logout).
- [x] Topbar: UTC clock now visible on mobile + notification bell with cached unread badge.
- [x] Dashboard: stats already 2x2 on mobile (`col-6 col-md-4 col-xl-3`); next-task alert → `.hazard-bar` (Start work [4.4] / Open [else] + Navigate→Google Maps when coords snapshot); today's tasks → touch `.mobile-task-card` list on <lg (table kept ≥lg).
- [x] Task work console: sticky `.work-action-bar` above bottom nav — Punch in & start ↔ Finish task by status, mirrors in-page actions, scrolls to finish card; evidence toolbar → 48px `.btn-touch` (camera + gallery); checklist/subtask rows 48px.
- [x] Attendance log: mobile event cards (user, type, time, geofence badge, flags) on <lg; table ≥lg. Clock-in hero skipped — page is team log for 5.1 holders; field users clock via task work page.
- [x] Approval queue: mobile cards with 48px Approve / Ask fix / Reject / Open task buttons. **Route bug fixed**: `/supervisor-only-approvals` duplicate `name('approvals')` was overriding the real queue route (admins got 403, stub page rendered) — name removed, queue now reachable.
- [x] Personnel: mobile contact cards with 48px `tel:` (when phone set) / `mailto:` / Edit.
- [x] Properties: mobile cards with Maps link (when coords) + Edit.
- [x] Filter pills (`.filter-pills`, 48px, horizontal scroll, no scrollbar) on personnel (All / Active / each role) + properties (All / Missing coords / Unassigned / first 3 categories) — links preserve other query params; active pill highlighted.
- [x] Shifts: mobile shift cards (worker, time range, property, status + late/early/missed badges, worked/break/overtime summary) with 48px Set-status control for 5.2 holders; table ≥lg.
- [x] Incident quick-report: single-column form (already) + `capture="environment"` camera on photos input + 48px "Use my location" button (navigator.geolocation fills lat/lng, spinner while locating).
- [x] Bento tap-to-filter: satisfied via existing navigation — stat cards already link to filtered destinations (server-side); no same-page JS filter needed.

## Verified (browser, 375px + 320px emulation)

- Bottom nav flex 64px, 63px tabs, 52px FAB, z 1030; body padding 80px; no horizontal scroll at 320px.
- FAB sheet opens/closes, permission-gated (cleaner sees only Notifications); Menu opens drawer; sheet handle closes.
- Dashboard: 2x2 stats (184px cols @375, 156px @320), 98px task cards, topbar clock + bell.
- Work bar: 65px above nav, 48px button, label tracks status (in_progress → "Finish task"), click focuses remarks.
- Approval queue: admin reaches real page (was 403), card with 48px buttons; approve form → task approved (verified in DB), card removed.
- Personnel: 6 cards, 48px mailto + edit buttons; no tel button when no phone. Properties: 4 cards, maps/edit buttons.
- 153/153 tests green (after all view changes).
- Topbar bell hidden <992px (rule `components.css:934`; earlier browser check false-negative — emulation had reset to desktop after browser restart).
- Pills: personnel pill click → `?role=1` filters list (Supervisor pill active, 1 card); properties pills render (All active), no h-scroll. Shifts: 2 mobile cards, table hidden. Incident: GPS button 48px, capture=environment.

## Next

1. None from spec — roadmap items 2.1–2.6 all implemented (shift "swipeable" interpreted as native scroll, matching attendance pattern).
2. Optionally: teams page cards (2.6 mentions teams) if needed.
