# Navigation Sidebar Restructuring & Native Mobile Drawer Specification

> **Intelligence Source**: `UI/UX Pro Max` Mobile Navigation & Information Architecture Engine  
> **Primary Goal**: Group navigation items into clear operational categories, moving configuration/setting items to the final section, and turning the mobile sidebar into a native-feeling mobile drawer.  
> **Target Platform**: Responsive Web & Mobile Web (Laravel Blade + Bootstrap 5 + Dispatch Navy Industrial Token System)  

---

## 1. Information Architecture & Section Hierarchy

### Section 1: Operations (Primary Core)
- **Dashboard** (`route('dashboard')`)
- **Task List** (`route('tasks')`)
- **My Tasks** (`route('tasks.my')`)
- **Properties** (`route('properties')`)
- **Calendar** (`route('calendar')`)

### Section 2: Field Section (Field Ops & Attendance Execution)
- **Shifts** (`route('shifts')`)
- **Attendance** (`route('attendance')`)
- **Approvals** (`route('approvals')`)
- **Incidents** (`route('incidents')`)

### Section 3: System & Configuration (Last Section)
- **Personnel** (`route('personnel')`)
- **Teams** (`route('teams')`)
- **Branches** (`route('branches')`)
- **Categories** (`route('property-categories')`)
- **Tags** (`route('property-tags')`)
- **Task Types** (`route('task-types')`)
- **Checklists** (`route('checklists')`)
- **Recurrences** (`route('recurrences')`)
- **Reports** (`route('reports')`)
- **Notifications** (`route('notifications')`)
- **Audit log** (`route('audit')`)
- **Settings** (`route('settings')`)
- **Permissions** (`route('permissions')`)

---

## 2. Native Mobile Sidebar Drawer Specifications (`< 992px`)

1. **Offcanvas Panel Geometry**:
   - `position: fixed; inset: 0 auto 0 0; width: 280px; max-width: 85vw; z-index: 1050;`
   - Spring physics animation: `transform: translateX(-100%)` → `transform: translateX(0)` over `0.3s cubic-bezier(0.32, 0.72, 0.24, 1)`.
2. **Glassmorphic Backdrop Overlay**:
   - `position: fixed; inset: 0; background: rgba(10, 20, 31, 0.7); backdrop-filter: blur(8px); z-index: 1040;`
3. **Mobile Header & Close Controls**:
   - Header includes a dedicated close button (`.btn-close-white #sidebar-close`) for 1-tap exit.
   - User profile card displays active user avatar initial, name, and role badge (ADMIN / SUPERVISOR / CLEANER).
4. **Touch Micro-Interactions**:
   - Sidebar links feature `min-height: 44px` touch targets with rounded corners (`border-radius: 8px`).
   - Tapping any sidebar link automatically closes the drawer on mobile screens.
