# High-Density Viewport-Optimized Redesign Specification
## Task Scheduling Form (`resources/views/pages/task-create.blade.php`)

> **Intelligence Source**: `UI/UX Pro Max` High-Density Form Engine  
> **Primary Goal**: Fit maximum task creation components (Property, Location, Schedule, Task Type, Priority, Managers, Assignees, Checklists, Sub-tasks, Recurrence) into a single compact viewport without losing clarity or field context.  
> **Target Platform**: Responsive Web (Laravel Blade + Bootstrap 5 + Select2 + Industrial Field-Ops Token System)  

---

## 1. Codebase Audit & Space Wastage Findings

### Existing Deficiencies in `task-create.blade.php`:
1. **Vertical Card Stack Overload**: The form renders 6 separate `<div class="card shadow-sm mb-3">` containers vertically stacked.
2. **Excessive Spacing**: Each card introduces an independent header (36px), card body padding (24px), and bottom margin (16px), wasting over **450px of vertical space** purely on container borders and margins!
3. **Redundant Location Inputs**: Location fields (`Property name snapshot`, `Address snapshot`, `Latitude`, `Longitude`) take up 2 separate rows of card space even though 95% of tasks auto-fill these from the property selection.
4. **Action Button Positioning**: The primary "Create Task" button is placed at the very bottom of line 215, requiring users to scroll past 1,200px of form fields before submitting.

---

## 2. Key Objectives & Design Metrics

| Design Metric | Current Baseline | Proposed Compact Target | Improvement Goal |
|---|---|---|---|
| **Total Form Height (Desktop)** | ~1,250px (Requires 2+ viewport scrolls) | **~580px** (Fits inside 1 single viewport) | **54% reduction** in vertical height |
| **Form Container Layout** | 6 vertically stacked standalone cards | **Dual-Panel 2-Column Compact Grid** (7/12 & 5/12 split) | Multi-column high data density |
| **Location Auto-Fill Fields** | 4 visible inputs taking 2 rows | **Collapsible Compact Location Drawer** (Auto-collapsed) | Hides redundant snapshot fields |
| **Primary Action Placement** | Bottom of page only | **Sticky Action Bar** (Header + Floating Action Footer) | Instant 1-click submission |

---

## 3. Screen Structure & Layout Hierarchy

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ HEADER: Schedule Task                             [ ✖ Cancel ] [ ▶ CREATE ] │
├──────────────────────────────────────────┬──────────────────────────────────┤
│ LEFT PANEL: CORE TASK & ASSIGNMENT (7/12)│ RIGHT PANEL: CHECKLIST & RULE (5)│
│ ┌──────────────────────────────────────┐ │ ┌──────────────────────────────┐ │
│ │ 1. PROPERTY & LOCATION SNAPSHOT      │ │ │ 3. CHECKLIST & SUB-TASKS     │ │
│ │ Property: [ Select Property... 🔍]   │ │ │ Template: [ From Task Type ] │ │
│ │ [📍 Autofilled: Tower B · Floor 4 ✏️] │ │ │ Sub-tasks:                    │ │
│ ├──────────────────────────────────────┤ │ │ [ Restroom Sanitization   ✖ ]│ │
│ │ 2. SCHEDULE & TYPE                   │ │ │ [ Stock Supplies          ✖ ]│ │
│ │ Type: [ Deep Clean ]  Starts: [09:00]│ │ │ [ + Add Sub-task ]           │ │
│ │ Ends: [10:30]  Dur: [90m]  Pri: [High]│ │ ├──────────────────────────────┤ │
│ ├──────────────────────────────────────┤ │ │ 4. RECURRENCE & OVERRIDES    │ │
│ │ 3. PEOPLE & TEAM ASSIGNMENTS         │ │ │ [x] Enable Recurrence        │ │
│ │ Manager: [ Manager A ]               │ │ │ Rule: [ FREQ=WEEKLY;INT=1  ] │ │
│ │ Assignees: [ Cleaner 1 x ] [ Cl 2 x ] │ │ │ [ ] Override Conflicts       │ │
│ │ Team: [ Team Alpha ]  [x] Approval   │ │ └──────────────────────────────┘ │
│ └──────────────────────────────────────┘ │                                  │
└──────────────────────────────────────────┴──────────────────────────────────┘
```

---

## 4. Component-by-Component Specifications

### 4.1 Component 1: Clean Header & Bottom Action Controls
- **Header**: Contains page title (`Schedule New Task`) and section eyebrow (`Tasks · Create`) only — header action buttons have been removed for a clean visual canvas.
- **Bottom Action Bar**: `Cancel` and `Create Task` sit **side-by-side on the SAME line** (`<div class="d-flex gap-2 w-100 mt-3">`) at the bottom of the right panel, ensuring 1-tap submission on both mobile and desktop.

### 4.2 Component 2: Property Selection & Inline Edit Snapshots Button
- **Inline Header Button**: The "Edit Snapshots" toggle link is placed directly on the right side of the `Property *` label header, eliminating the separate row box below the dropdown.
- **Auto-Fill Location Summary**: Displays an inline monospaced green badge (`📍 Commercial Tower B — 1 Queen St`) only when a property is selected.
- **Collapsible Drawer**: Tapping "Edit Snapshots" expands the 4-column coordinate drawer for manual one-off overrides.

### 4.3 Component 3: Schedule, Duration & Priority Matrix (Side-by-Side Mobile View)
- **Side-by-Side Mobile Layout**: `Starts At` (`#scheduled_start_at`) and `Ends At` (`#scheduled_end_at`) use `col-6 col-md-6` so they sit side-by-side on the SAME line on mobile screens instead of stacking vertically.
- **Ultra-Compact Card Gaps**: Reduced card bottom margins (`mb-2`) and grid spacing (`g-2`) for maximum data compression.

### 4.4 Component 4: People & Team Assignments Hub
- **Manager**: Select dropdown for assigned supervisor.
- **Assignees**: Multi-select dropdown with compact tag pills (`Cleaner 1 ✖`, `Cleaner 2 ✖`).
- **Team**: Optional team assignment dropdown.
- **Approval Switch**: Compact `form-switch` toggle ("Approval Required").

### 4.5 Component 5: Checklist & Dynamic Sub-Tasks Panel
- **Checklist Template**: Select dropdown to pick predefined checklist templates.
- **Dynamic Sub-Task Rows**: Compact single-line input rows (`input-group-sm`) with a 1-tap delete button (`bi-x`) and `+ Add sub task` button.

### 4.6 Component 6: Recurrence & Conflict Override Drawer
- **Recurrence Toggle**: Compact `form-switch` checkbox ("Enable Recurrence").
- **Rule Input**: Inline monospaced rule field (`FREQ=WEEKLY;INTERVAL=1`).
- **Conflict Override Checkbox**: Checkbox to allow scheduling despite worker availability/conflict warnings.

---

## 5. Viewport Density Optimization Techniques Applied

1. **Integrated Form Panels**: Merged 6 standalone cards into **2 unified high-density cards** (`col-lg-7` and `col-lg-5`).
2. **Condensed Input Heights**: Utilized `.form-control-sm` and `.form-select-sm` (34px height) with 10px monospaced uppercase labels.
3. **Smart Field Hiding**: Location snapshot inputs (address, lat, lng) are hidden in a collapsible drawer since they are auto-populated from property selection 95% of the time.
4. **Inline Form Controls**: Converted boolean options into compact `form-switch` toggles positioned beside input labels.

