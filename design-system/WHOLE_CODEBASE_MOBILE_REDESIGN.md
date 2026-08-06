# Whole-Codebase Mobile-First UI/UX Redesign Specification
## CleanWay Operations Console (`cleanway_backend`)

> **Intelligence Source**: `UI/UX Pro Max` Deep Codebase Scan  
> **Target Scope**: Entire Laravel Blade + Bootstrap 5 View Layer (`resources/views/*`) & CSS Tokens System (`public/css/*`)  
> **Primary Persona Focus**: Field Cleaners, Field Supervisors, Mobile Managers, and Mobile Dispatchers  

---

## 1. Codebase Audit Findings & Mobile Pain Points

Following a comprehensive scan of all 34 Blade pages, layout partials, and stylesheets in `resources/views/` and `public/css/`, the key mobile UX limitations identified are:

```
┌───────────────────────────────────────────────────────────────────────────────────────┐
│                      CURRENT STATE vs. MOBILE-FIRST VISION                            │
├───────────────────────────────────────────────────┬───────────────────────────────────┤
│ CURRENT CODEBASE AUDIT FINDINGS                   │ PROPOSED MOBILE-FIRST SOLUTION    │
├───────────────────────────────────────────────────┼───────────────────────────────────┤
│ 1. Desktop Left Sidebar ONLY (`layouts/app`)      │ Fixed 5-Tab Mobile Bottom Navigation Bar │
│    Requires hamburger menu open on every page tap │ 1-tap thumb access to primary modules│
├───────────────────────────────────────────────────┼───────────────────────────────────┤
│ 2. Desktop Multicolumn Tables (`.table`)          │ Responsive Touch Cards & Bento    │
│    Squished text & horizontal scroll on <576px   │ Touch-friendly stacked cards (48px)│
├───────────────────────────────────────────────────┼───────────────────────────────────┤
│ 3. Task Execution View (`pages/task-work.blade`)  │ Sticky Action Footer & Wizard Step│
│    31KB monolithic desktop form layout            │ 1-thumb "Clock In" & "Upload" FAB │
├───────────────────────────────────────────────────┼───────────────────────────────────┤
│ 4. Small Touch Targets (<36px height)             │ WCAG AAA Touch Minimum (48px x 48px)│
│    Buttons and table actions hard to tap on go   │ Enforced `--cw-touch-target-min`  │
├───────────────────────────────────────────────────┼───────────────────────────────────┤
│ 5. Centered Modals Obscure Screen Context         │ Slide-up Mobile Bottom Sheets     │
│    Small close buttons hard to hit on mobile      │ Thumb-reachable swipeable sheet   │
└───────────────────────────────────────────────────┴───────────────────────────────────┘
```

---

## 2. Whole-Codebase Module Redesign Roadmap

### 2.1 Core Shell & Layout (`resources/views/layouts/app.blade.php`)
- **Mobile Sticky Header**: Add a dedicated compact 48px mobile topbar with live UTC clock (`data-clock`), GPS accuracy status indicator (Green/Amber dot), and Notification badge.
- **Fixed Mobile Bottom Navigation**: Render `@include('partials.mobile-bottom-nav')` fixed at screen bottom on viewports `< 992px`.
- **Safe-Area Inset Handling**: Automatically adjust page bottom padding with `padding-bottom: calc(64px + env(safe-area-inset-bottom))`.

### 2.2 Main Dashboard (`resources/views/dashboard.blade.php`)
- **2x2 Mobile Bento Stat Grid**: Transform the 4-column `widgets['stats']` row into a touchable 2x2 Bento Stat Grid on mobile screens (`d-grid d-lg-none`). Tapping any stat filters the task list below.
- **Next Task Sticky Hazard Bar**: Convert next task alert into a full-width safety-orange hazard card anchored above the feed with direct "Start Work" and "Navigate" buttons.
- **Today's Tasks Touch Feed**: Replace HTML `<table>` with `mobile-task-card` components featuring 48px tap targets for status transitions.

### 2.3 Field Task Execution & Work Console (`resources/views/pages/task-work.blade.php` & `partials/evidence-upload.blade.php`)
- **Sticky Execution Action Footer**: Anchor a fixed bottom action bar containing the primary step button (*Start Work* -> *Upload Before Photo* -> *Complete Checklist* -> *Upload After Photo* -> *Finish Task*).
- **Camera-Optimized Photo Upload**: Upgrade `evidence-upload.blade.php` with large 64px camera touch buttons and instant full-screen image previews.
- **Touch Checklist Items**: Convert checklist checkboxes into large 48px touch rows with haptic visual feedback when checked off.

### 2.4 Attendance, GPS & Shifts (`resources/views/pages/attendance.blade.php` & `shifts.blade.php`)
- **1-Tap Clock-In Hero Widget**: Top-of-screen prominent clock-in/clock-out card displaying live GPS geofence distance, current shift status, and big safety-orange "CLOCK IN NOW" touch button.
- **Shift Cards**: Replace desktop schedule list with swipeable daily shift cards.

### 2.5 Approvals & Incidents Queue (`resources/views/pages/approvals.blade.php`, `incidents.blade.php`, `incident-create.blade.php`)
- **Swipeable Approval Cards**: Quick-action Approve (Green) / Reject (Red) 48px touch buttons directly on mobile queue items.
- **Incident Quick-Report Form**: Single-column mobile-friendly form with camera attachment button and auto-populated GPS location tags.

### 2.6 Personnel, Properties & Teams (`resources/views/pages/personnel.blade.php`, `properties.blade.php`, `teams.blade.php`)
- **Sticky Search & Category Filter Pills**: Horizontal scrollable filter pills (`All`, `Active`, `Cleaners`, `Supervisors`) above a mobile card stream.
- **Personnel Contact Cards**: Quick 1-tap `tel:` and `mailto:` action buttons on personnel cards for field managers.

---

## 3. UI/UX Pro Max Design Token Enhancements (`public/css/tokens.css` & `components.css`)

### 3.1 CSS Custom Properties (`public/css/tokens.css`)

```css
:root {
  /* ============ Touch & Mobile Ergonomics Tokens ============ */
  --cw-touch-target-min: 48px;             /* WCAG AAA touch minimum */
  --cw-touch-gap-min: 8px;                 /* Minimum gap between touch controls */
  --cw-bottom-nav-height: 64px;            /* Height of sticky bottom tabbar */
  --cw-safe-area-bottom: env(safe-area-inset-bottom, 0px); /* iOS notch/bar handling */
}
```

