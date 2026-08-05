<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Web\ApprovalController;
use App\Http\Controllers\Web\AttendanceController;
use App\Http\Controllers\Web\AuditLogController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\CalendarController;
use App\Http\Controllers\Web\ChecklistTemplateController;
use App\Http\Controllers\Web\EvidenceController;
use App\Http\Controllers\Web\FcmTestController;
use App\Http\Controllers\Web\IncidentController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\PersonnelController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\PlacesController;
use App\Http\Controllers\Web\PropertyAssignmentController;
use App\Http\Controllers\Web\PropertyCategoryController;
use App\Http\Controllers\Web\PropertyController;
use App\Http\Controllers\Web\PropertyTagController;
use App\Http\Controllers\Web\RecurrenceController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\ShiftController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\TaskTypeController;
use App\Http\Controllers\Web\TeamController;
use Illuminate\Support\Facades\Route;

// Public routes — no auth, no permission middleware.
Route::get('/', fn () => redirect()->route('login'));

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Forgot / reset password (guest only).
Route::middleware('guest')->group(function (): void {
    Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Authenticated + permission-protected routes.
Route::middleware('auth')->prefix('admin')->group(function (): void {
    Route::middleware('permission:4.1')->get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::middleware('permission:7.1')->get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::middleware('permission:7.2')->group(function (): void {
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/exports/{job}/download', [ReportController::class, 'download'])->name('reports.download');
    });

    Route::middleware('permission:9.1')->get('/audit', [AuditLogController::class, 'index'])->name('audit');

    Route::middleware('permission:3.1')->group(function (): void {
        Route::get('/properties', [PropertyController::class, 'index'])->name('properties');
        Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
        Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
        Route::get('/places/autocomplete', [PlacesController::class, 'autocomplete'])->name('places.autocomplete');
        Route::get('/places/details', [PlacesController::class, 'details'])->name('places.details');
        Route::get('/properties/options', [PropertyController::class, 'options'])->name('properties.options');
        Route::get('/property-categories', [PropertyCategoryController::class, 'index'])->name('property-categories');
        Route::get('/property-tags', [PropertyTagController::class, 'index'])->name('property-tags');
    });

    Route::middleware('permission:3.2')->post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::middleware('permission:3.3')->group(function (): void {
        Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
        Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
        Route::post('/properties/{property}/retry-geocode', [PropertyController::class, 'retryGeocode'])->name('properties.retry-geocode');
    });

    Route::middleware('permission:3.4')->group(function (): void {
        Route::post('/property-categories', [PropertyCategoryController::class, 'store'])->name('property-categories.store');
        Route::put('/property-categories/{category}', [PropertyCategoryController::class, 'update'])->name('property-categories.update');
    });

    Route::middleware('permission:3.5')->group(function (): void {
        Route::post('/property-tags', [PropertyTagController::class, 'store'])->name('property-tags.store');
        Route::put('/property-tags/{tag}', [PropertyTagController::class, 'update'])->name('property-tags.update');
        Route::post('/property-tags/merge', [PropertyTagController::class, 'merge'])->name('property-tags.merge');
        Route::post('/properties/bulk/tags', [PropertyTagController::class, 'bulkAssign'])->name('property-tags.bulk-assign');
        Route::delete('/properties/bulk/tags', [PropertyTagController::class, 'bulkRemove'])->name('property-tags.bulk-remove');
    });

    Route::middleware('permission:3.6')->group(function (): void {
        Route::post('/properties/{property}/assignments', [PropertyAssignmentController::class, 'store'])->name('property-assignments.store');
        Route::delete('/property-assignments/{assignment}', [PropertyAssignmentController::class, 'destroy'])->name('property-assignments.destroy');
    });

    // Own notifications — any authenticated user.
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/devices', [\App\Http\Controllers\Api\V1\DeviceController::class, 'store'])->name('devices.store');

    // Ghost FCM test page — admin only, intentionally not linked anywhere.
    Route::middleware('role:0')->group(function (): void {
        Route::get('/_fcm-test', [FcmTestController::class, 'index'])->name('fcm-test');
        Route::post('/_fcm-test', [FcmTestController::class, 'send'])->name('fcm-test.send');
    });

    Route::middleware('permission:4.1')->group(function (): void {
        Route::get('/my-tasks', [TaskController::class, 'my'])->name('tasks.my');
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::get('/tasks/{task}/work', [TaskController::class, 'work'])->name('tasks.work');
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
        Route::get('/recurrences', [RecurrenceController::class, 'index'])->name('recurrences');
        Route::get('/task-types', [TaskTypeController::class, 'index'])->name('task-types');
        Route::get('/checklists', [ChecklistTemplateController::class, 'index'])->name('checklists');
    });

    // Task list (all users' tasks) — permitted users only.
    Route::middleware('permission:4.9')->get('/tasks', [TaskController::class, 'index'])->name('tasks');

    Route::middleware('permission:4.2')->group(function (): void {
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::post('/recurrences', [RecurrenceController::class, 'store'])->name('recurrences.store');
        Route::post('/recurrences/{recurrence}/generate', [RecurrenceController::class, 'generateNow'])->name('recurrences.generate-now');
    });

    Route::middleware('permission:4.3')->group(function (): void {
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::post('/tasks/{task}/assign', [TaskController::class, 'assign'])->name('tasks.assign');
        Route::delete('/tasks/{task}/assignments/{assignment}', [TaskController::class, 'unassign'])->name('tasks.unassign');
    });

    Route::middleware('permission:4.4')->post('/tasks/{task}/transition', [TaskController::class, 'transition'])->name('tasks.transition');

    Route::middleware('permission:4.6')->delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    Route::middleware('permission:4.7')->group(function (): void {
        Route::post('/task-types', [TaskTypeController::class, 'store'])->name('task-types.store');
        Route::put('/task-types/{taskType}', [TaskTypeController::class, 'update'])->name('task-types.update');
    });

    Route::middleware('permission:4.8')->group(function (): void {
        Route::post('/checklists', [ChecklistTemplateController::class, 'store'])->name('checklists.store');
        Route::put('/checklists/{template}', [ChecklistTemplateController::class, 'update'])->name('checklists.update');
    });

    Route::middleware('permission:4.4')->group(function (): void {
        Route::post('/tasks/{task}/evidence', [TaskController::class, 'uploadEvidence'])->name('tasks.evidence');
        Route::get('/evidence/{evidence}', [EvidenceController::class, 'view'])->name('evidence.view')->middleware('permission:4.1');
        Route::post('/tasks/{task}/subtasks', [TaskController::class, 'storeSubtask'])->name('tasks.subtasks.store');
        Route::post('/tasks/{task}/subtasks/{subtask}/toggle', [TaskController::class, 'toggleSubtask'])->name('tasks.subtasks.toggle');
        Route::post('/tasks/{task}/check-in', [TaskController::class, 'workCheckIn'])->name('tasks.work-checkin');
        Route::post('/tasks/{task}/complete', [TaskController::class, 'completeTask'])->name('tasks.complete');
    });

    Route::middleware('permission:4.5')->group(function (): void {
        Route::get('/approvals', [ApprovalController::class, 'queue'])->name('approvals');
        Route::post('/approvals/{task}/decide', [ApprovalController::class, 'decide'])->name('approvals.decide');
    });

    Route::middleware('permission:5.1')->group(function (): void {
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    });

    Route::middleware('permission:5.2')->group(function (): void {
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
    });

    Route::middleware('permission:6.1')->get('/attendance/corrections', [AttendanceController::class, 'corrections'])->name('attendance.corrections');
    Route::middleware('permission:6.2')->post('/attendance/corrections/{correction}/decide', [AttendanceController::class, 'decideCorrection'])->name('attendance.corrections.decide');

    Route::middleware('permission:8.1')->get('/incidents', [IncidentController::class, 'index'])->name('incidents');
    Route::middleware('permission:8.2')->group(function (): void {
        Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
        Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
        Route::post('/incidents/{incident}/transition', [IncidentController::class, 'transition'])->name('incidents.transition');
    });

    Route::middleware('role:1,2')->get('/cleaner-tools', [DashboardController::class, 'cleanerTools'])->name('cleaner-tools');

    Route::middleware('permission:2.1')->group(function (): void {
        Route::get('/personnel', [PersonnelController::class, 'index'])->name('personnel');
        Route::get('/personnel/create', [PersonnelController::class, 'create'])->name('personnel.create');
        Route::get('/personnel/{user}/edit', [PersonnelController::class, 'edit'])->name('personnel.edit');
    });

    Route::middleware('permission:2.2')->post('/personnel', [PersonnelController::class, 'store'])->name('personnel.store');
    Route::middleware('permission:2.3')->group(function (): void {
        Route::put('/personnel/{user}', [PersonnelController::class, 'update'])->name('personnel.update');
        Route::post('/personnel/{user}/password', [PersonnelController::class, 'changePassword'])->name('personnel.password');
        Route::post('/personnel/{user}/toggle-active', [PersonnelController::class, 'toggleActive'])->name('personnel.toggle-active');
    });
    Route::middleware('permission:2.4')->delete('/personnel/{user}', [PersonnelController::class, 'destroy'])->name('personnel.destroy');

    Route::middleware('permission:2')->group(function (): void {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
        Route::get('/teams', [TeamController::class, 'index'])->name('teams');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.store');
        Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.destroy');
    });

    Route::middleware('permission:1')->group(function (): void {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/settings/users', [DashboardController::class, 'users'])->name('settings.users')->middleware('permission:1.1');
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions')->middleware('permission:1.2');
        Route::post('/permissions/{user}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:1.2');
    });
});

// Combo: permission AND role on one route.
Route::get('/supervisor-only-approvals', [DashboardController::class, 'approvals'])->name('approvals')
    ->middleware(['auth', 'permission:4.5', 'role:1']);
