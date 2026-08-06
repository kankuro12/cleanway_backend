# CleanWay Field Operations Dashboard Redesign Specification
> **Scope**: Dashboard Interface (`resources/views/dashboard.blade.php`)  
> **Design Framework**: `UI/UX Pro Max` Industrial Operations & Mobile-First Touch System  
> **Target Audience**: Cleaners, Field Supervisors, Mobile Managers, Dispatchers  

---

## 1. Executive Summary & Page Objectives

The CleanWay Operations Dashboard acts as the real-time operational nerve center. The primary goal of this redesign is to transition the interface from a desktop-centric admin layout into a **Mobile-First Field Console** optimized for single-handed smartphone use while retaining a data-dense command view on desktop monitors.

### Primary Objectives:
- **Mobile Ergonomics**: Place primary action controls in the natural thumb reach area (bottom 35% of the mobile screen).
- **High Information Density**: Present key operational metrics, active task alerts, and urgent attention items without requiring excessive scrolling.
- **Glanceability**: Use color contrast, status pills, and monospaced typography so field workers can scan status in direct sunlight or low-light conditions.

---

## 2. Key UX Principles & Anti-Patterns

### ✅ Requirements (What to Do):
1. **48px Minimum Touch Targets**: Ensure every button, card link, tab, and form trigger meets or exceeds the 48px × 48px touch target standard.
2. **2x2 Mobile Bento Grid**: Arrange core statistics in a 2x2 grid on mobile viewports rather than a long vertical list.
3. **Sticky Next Task Hazard Bar**: Highlight the user's next scheduled task with high-contrast safety-orange styling and immediate action buttons (*Start Work*, *Navigate*).
4. **Touch Card Task Feed**: Transform tabular list data into touch-friendly cards on mobile devices, with status-tinted borders and 1-tap action triggers.
5. **Severity-Coded Attention Queue**: Highlight GPS exceptions, missed check-ins, and critical incidents with clear severity pills (*Critical*, *Warning*, *Info*).
6. **Safe-Area Insets**: Respect device safe-area insets (iOS home bar / Android gesture nav) to prevent fixed elements from obscuring content.

### ❌ Anti-Patterns (What NOT to Do):
1. **No Emojis as UI Icons**: Use vector icons exclusively (Bootstrap Icons / Heroicons).
2. **No Tiny Touch Targets**: Never use small desktop-sized buttons or tightly packed links on mobile screens.
3. **No Forced Horizontal Scrolling**: Tables must never force horizontal scrolling on mobile viewports; reflow cleanly into stacked touch cards.
4. **No Desktop Modals on Mobile**: Avoid centered desktop popup modals for quick mobile actions; use slide-up bottom sheets instead.
5. **No Color-Only Status Indication**: Always pair status colors with monospaced text labels and status dots for full accessibility compliance.

---

## 3. Screen Structure & Layout Hierarchy

### Layout Breakdown:
1. **Header & Context Bar**:
   - Section eyebrow ("Overview") and primary page title ("Today's operations").
   - Live UTC shift clock indicator and active role chip.

2. **Operations Summary Bento Grid**:
   - Mobile (`< 992px`): 2x2 compact grid displaying Total Tasks, In Progress, Pending Start, and Attention Required.
   - Desktop (`>= 992px`): 4-column horizontal stat card row.

3. **Active Operation Hazard Banner**:
   - Prominent full-width alert banner for the immediate next task.
   - Includes task title, property location, scheduled start time, and primary action buttons (*Start Work*, *Navigate*).

4. **Dual Data Feed Split**:
   - **Left Column (7/12 width)**: Today's Scheduled Tasks feed. Mobile view renders touch cards with left status border accents; desktop view renders high-density monospaced table rows.
   - **Right Column (5/12 width)**: Needs Attention Queue. Displays real-time GPS exceptions, unresolved incidents, and pending approvals categorized by section.

---

## 4. Component Design Specifications

### Component 1: Operations Header
- **Placement**: Top of content canvas.
- **Content**: Section eyebrow, main title, current date/shift badge.
- **Behavior**: Stays visible; on mobile, aligns vertically to conserve horizontal space.

### Component 2: Summary Bento Cards
- **Placement**: Directly below header.
- **Visuals**: Light surface card with left accent indicator bar on hover/active. Large bold numeral with monospaced uppercase label underneath.
- **Interaction**: Tapping any card filters the task feed below by that specific status.

### Component 3: Next Task Hazard Banner
- **Placement**: Above main data feeds.
- **Visuals**: Safety-orange tinted background with hazard icon, bold task name, location subtitle, and timestamp.
- **Actions**: Primary action button (*Start Work*) styled in safety orange with dark text; secondary action button (*Navigate*) opening Google Maps location.

### Component 4: Mobile Task Cards
- **Placement**: Left panel on viewports `< 992px`.
- **Visuals**: Individual card for each task with a 4px colored left border reflecting current status (Blue = Assigned, Orange = In Progress, Green = Completed).
- **Metadata**: Reference number badge, task title, scheduled time, assigned personnel names.
- **Action Strip**: Bottom row with 1-tap *View Details* and *Start Work* buttons.

### Component 5: Attention Stream Cards
- **Placement**: Right panel.
- **Visuals**: Grouped cards with section header (e.g. "GPS Exceptions", "Unresolved Incidents").
- **Content**: Event reporter/user name, description, severity status badge (*Critical* = Red, *Warning* = Amber).
- **Actions**: 1-tap *Review* button opening detail context.

---

## 5. Step-by-Step Implementation Roadmap

1. **Tokens & Touch Setup**: Verify custom property touch tokens (48px minimum height/width) and bottom navigation clearance rules in global stylesheets.
2. **Header & Bento Grid Integration**: Structure dashboard header and configure 2x2 Bento grid responsive display rules for mobile devices.
3. **Hazard Banner Refinement**: Enhance the next task hazard bar layout with clear action hierarchy and map navigation links.
4. **Mobile Task Cards**: Implement touch card feed for mobile screens while preserving the monospaced table layout for desktop monitors.
5. **Attention Queue Enhancement**: Upgrade attention item list items with clear severity pills and review triggers.
6. **Cross-Device Verification**: Test responsive reflow across 360px, 375px, 414px, 768px, and 1200px viewports.

