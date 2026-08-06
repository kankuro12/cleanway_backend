<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Attendance\AttendanceRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ViewSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pages_render(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin);

        $property = \App\Models\Property::create(['name' => 'P', 'address' => 'A']);
        $task = \App\Models\Task::create(['title' => 'T', 'status' => \App\Models\Task::STATUS_SCHEDULED]);

        $emptyPage = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);

        $data = fn () => [
            'property' => $property,
            'task' => $task,
            'tasks' => $emptyPage,
            'properties' => $emptyPage,
            'taskTypes' => $emptyPage,
            'checklists' => new Collection,
            'managers' => new Collection,
            'cleaners' => new Collection,
            'people' => new Collection,
            'teams' => new Collection,
            'assignees' => new Collection,
            'workers' => new Collection,
            'categories' => $emptyPage,
            'tags' => $emptyPage,
            'recurrences' => $emptyPage,
            'templates' => $emptyPage,
            'notifications' => $emptyPage,
            'incidents' => $emptyPage,
            'events' => $emptyPage,
            'requests' => $emptyPage,
            'logs' => $emptyPage,
            'shifts' => $emptyPage,
            'exports' => new Collection,
            'settings' => new Collection,
            'users' => $emptyPage,
            'roles' => [],
            'rules' => app(AttendanceRules::class),
            'type' => 'tasks',
            'report' => ['headers' => ['A'], 'rows' => []],
            'widgets' => ['stats' => [], 'attention' => [], 'today' => new Collection],
            'current' => $emptyPage,
            'finished' => $emptyPage,
            'tab' => 'current',
            'lastPunch' => null,
            'users' => $emptyPage,
            'selected' => $admin,
            'permissionTree' => [['section' => 'Tasks', 'permissions' => [['key' => '4.1', 'label' => 'Tasks > View', 'role_default' => true]]]],
            'overrides' => new \Illuminate\Support\Collection,
            'checklistEnabled' => false,
            'errors' => new \Illuminate\Support\ViewErrorBag,
        ];

        $views = [
            'pages.property-create', 'pages.property-edit', 'pages.task-create', 'pages.task-edit',
            'pages.calendar', 'pages.checklists', 'pages.task-types', 'pages.recurrences',
            'pages.attendance-corrections', 'pages.incident-create', 'pages.approval-queue',
            'pages.property-categories', 'pages.property-tags', 'pages.notifications', 'pages.shifts',
            'pages.attendance', 'pages.incidents', 'pages.audit', 'pages.reports', 'pages.settings',
            'pages.personnel', 'pages.tasks', 'pages.tasks-cleaner', 'pages.properties', 'pages.fcm-test', 'pages.task-work',
            'pages.permissions', 'dashboard',
        ];

        foreach ($views as $view) {
            try {
                view($view, $data())->render();
            } catch (\Throwable $e) {
                $this->fail("View {$view} failed: {$e->getMessage()}");
            }
        }

        $this->assertTrue(true);
    }
}
