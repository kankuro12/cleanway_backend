# Asynchronous Zero-Reload Task Edit Specification

> **Intelligence Source**: `UI/UX Pro Max` Real-time Form & Axios Integration Engine  
> **Primary Goal**: Enable 100% zero-page-reload asynchronous updates for all field operations on the Task Edit view (`task-edit.blade.php`), using Axios AJAX requests with live visual toast feedback.  
> **Target Platform**: Responsive Web (Laravel Blade + Axios + Bootstrap 5 + Dispatch Navy Industrial Token System)  

---

## 1. Asynchronous Actions & Endpoints Covered

| Component / Action | HTTP Method | Endpoint Route | Dynamic UI Feedback (No Reload) |
|---|---|---|---|
| **Task Schedule & Details** | `PUT` | `route('tasks.update', $task)` | Updates header title, schedule inputs, displays live green success toast |
| **Move Status Transition** | `POST` | `route('tasks.transition', $task)` | Updates header status badge, status card title, appends new transition row to history table |
| **Add Assignee (Person / Team)** | `POST` | `route('tasks.assign', $task)` | Appends new assignment badge row to assignments container dynamically |
| **Remove Assignee** | `DELETE` | `route('tasks.unassign', [$task, $assignment])` | Animates removal of assignment row from DOM |
| **Add Sub-Task** | `POST` | `route('tasks.subtasks.store', $task)` | Appends new subtask row with toggle button and clears input |
| **Toggle Sub-Task (Done/Reopen)** | `POST` | `route('tasks.subtasks.toggle', [$task, $subtask])` | Toggles strike-through styling and checkmark icon in real-time |

---

## 2. Floating Live Status Toast Feedback System

- **Placement**: Fixed top-right floating toast banner (`#ajax-toast-banner`).
- **States**:
  - `Saving...`: Blue background with spinning loading indicator.
  - `Saved ✓`: Safety orange / emerald green success indicator (Auto-dismisses after 2.5s).
  - `Error ✖`: Crimson red warning badge displaying server error message.

---

## 3. Implementation Workflow

1. Update `TaskController.php` endpoints (`update`, `storeSubtask`, `toggleSubtask`, `transition`, `assign`, `unassign`) to return structured JSON responses when `$request->expectsJson()`.
2. Update `task-edit.blade.php` with unique container IDs (`#history-table-body`, `#assignments-list-container`, `#subtasks-list-container`, `#ajax-toast-banner`).
3. Attach jQuery/Axios submit handlers with `e.preventDefault()` to process all forms asynchronously without page refresh.

