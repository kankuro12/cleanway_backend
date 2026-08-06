# Dashboard Stat Cards Equal-Height & Visual Redesign Specification

> **Intelligence Source**: `UI/UX Pro Max` High-Density Dashboard Engine  
> **Primary Goal**: Resolve mobile height mismatch across dashboard summary cards by enforcing a 100% equal-height flexbox architecture with reserved label line space, while enhancing visual attractiveness through contextual color-tinted icon badges and industrial left indicator accents.  
> **Target Platform**: Responsive Mobile Web (Laravel Blade + Bootstrap 5 + Dispatch Navy Industrial Token System)  

---

## 1. Root Cause Analysis of Uneven Card Heights

### Why Cards Had Mismatched Heights on Mobile Screens:
1. **Inline / Unstretched Height**: Stat card anchors (`<a class="stat-card">`) did not have `height: 100%` specified, causing them to collapse to the natural height of their internal text.
2. **Column Flex Alignment**: Grid columns (`.col-6`) were missing `d-flex align-items-stretch`, allowing sibling cards in the same row to take different vertical pixel heights.
3. **Variable Label Line Wraps**: Labels with 1 line of text ("OVERDUE", "PERSONNEL") took ~16px height, whereas labels wrapping to 2 lines ("PENDING APPROVAL", "GPS EXCEPTIONS") took ~32px height, creating a 16px height imbalance between left and right column cards.

---

## 2. Flexbox Equal-Height Solution

```css
/* Container Column Stretch */
.stat-card-col {
  display: flex !important;
  align-items: stretch !important;
}

/* Stat Card Equal Height */
.stat-card {
  height: 100%;
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 108px;
}

/* Reserved Label Line Height (Fits 1 and 2 lines uniformly) */
.stat-card-label {
  min-height: 28px;
  display: flex;
  align-items: center;
  margin-top: 0.6rem;
}
```

---

## 3. Industrial Visual Theme Enhancements

| Stat Card Type | Left Indicator Border | Icon Badge Surface | Icon Color | Visual Focus |
|---|---|---|---|---|
| **Active Tasks / Tasks Today** | Safety Orange (`#FF6B1A`) | `rgba(255, 107, 26, 0.1)` | `#FF6B1A` | Core Shift Tasks |
| **Overdue / Open Incidents** | Crimson Red (`#E11D48`) | `rgba(225, 29, 72, 0.1)` | `#E11D48` | Urgent Exceptions |
| **Pending Approval** | Warning Amber (`#D97706`) | `rgba(217, 119, 6, 0.1)` | `#D97706` | Supervisor Action |
| **Personnel / Properties** | Info Blue (`#0284C7`) | `rgba(14, 165, 233, 0.1)` | `#0284C7` | Resource Tracking |
| **GPS Exceptions** | Flame Orange (`#EA580C`) | `rgba(234, 88, 12, 0.15)` | `#EA580C` | Location Alert |

---

## 4. Implementation Steps

1. Update `resources/views/dashboard.blade.php` to wrap stat card grid items in `d-flex align-items-stretch` and assign contextual theme classes (`stat-theme-accent`, `stat-theme-danger`, `stat-theme-warning`, `stat-theme-info`, `stat-theme-orange`).
2. Update `public/css/components.css` to add the equal-height flexbox properties, reserved label line heights, left accent borders, and micro-animations.

