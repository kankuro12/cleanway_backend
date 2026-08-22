<?php

namespace Tests\Feature;

use App\Domain\Tasks\AssignTask;
use App\Domain\Tasks\CreateTask;
use App\Domain\Tasks\GenerateRecurringTasks;
use App\Domain\Tasks\TransitionTaskStatus;
use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use App\Models\ChecklistTemplate;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Task;
use App\Models\TaskRecurrence;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class TaskModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
    }

    private function cleaner(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);
    }

    private function taskType(): TaskType
    {
        return TaskType::create([
            'name' => 'Routine Clean',
            'slug' => 'routine-clean',
            'default_estimated_duration_minutes' => 60,
            'default_priority' => 'medium',
            'approval_required' => true,
            'active' => true,
        ]);
    }

    private function property(): Property
    {
        return Property::create(['name' => 'Site A', 'address' => '1 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);
    }

    private function taskData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Weekly clean',
            'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
            'task_type_id' => $this->taskType()->id,
            'property_id' => $this->property()->id,
        ], $overrides);
    }

    public function test_create_task_with_assignee_notifies_and_snapshots(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();

        $response = $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData([
            'assignee_type' => 'user',
            'assignee_id' => $cleaner->id,
        ]));

        $response->assertRedirect();
        $task = Task::first();

        $this->assertSame(Task::STATUS_ASSIGNED, $task->status);
        $this->assertSame('Site A', $task->property_name_snapshot);
        $this->assertNotNull($task->check_in_radius_snapshot);
        $this->assertTrue($task->approval_required);
        $this->assertDatabaseHas('task_assignments', ['task_id' => $task->id, 'assignee_id' => $cleaner->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $cleaner->id, 'type' => 'task.assigned']);
    }

    public function test_task_defaults_to_approval_required(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();

        $task = app(CreateTask::class)->execute([
            'title' => 'Default approval',
            'property_id' => $this->property()->id,
            'scheduled_start_at' => now()->addDay()->toDateTimeString(),
            'assignee_ids' => [$cleaner->id],
        ], $supervisor)['task'];

        $this->assertTrue($task->approval_required);
    }

    public function test_create_task_with_one_time_location(): void
    {
        $supervisor = $this->supervisor();

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData([
            'property_id' => null,
            'property_name_snapshot' => 'Jobsite',
            'address_snapshot' => '42 Field Lane',
            'latitude_snapshot' => -36.8,
            'longitude_snapshot' => 174.7,
        ]))->assertRedirect();

        $task = Task::first();

        $this->assertNull($task->property_id);
        $this->assertSame('Jobsite', $task->property_name_snapshot);
        $this->assertSame(1, Task::count());
    }

    public function test_checklist_snapshot_is_immutable(): void
    {
        $supervisor = $this->supervisor();
        $template = ChecklistTemplate::create(['name' => 'Tpl', 'slug' => 'tpl']);
        $section = ChecklistSection::create(['checklist_template_id' => $template->id, 'name' => 'S1', 'sort_order' => 0]);
        ChecklistItem::create(['checklist_section_id' => $section->id, 'label' => 'Wipe glass', 'item_type' => 'yes_no', 'required' => true, 'sort_order' => 0]);

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData(['checklist_template_id' => $template->id]));

        $task = Task::first();
        $this->assertDatabaseHas('task_checklist_snapshots', ['task_id' => $task->id, 'item_label' => 'Wipe glass']);

        // Template changes must not touch the task snapshot.
        ChecklistItem::where('id', $section->items->first()->id)->update(['label' => 'CHANGED']);
        $section->items()->delete();

        $this->assertDatabaseHas('task_checklist_snapshots', ['task_id' => $task->id, 'item_label' => 'Wipe glass']);
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $task = Task::create([
            'title' => 'X',
            'status' => Task::STATUS_DRAFT,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHour(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        app(TransitionTaskStatus::class)->transition($task, Task::STATUS_APPROVED, $this->admin());
    }

    public function test_full_approval_flow(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $task = Task::create([
            'title' => 'Flow',
            'status' => Task::STATUS_SCHEDULED,
            'approval_required' => true,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHour(),
        ]);

        app(AssignTask::class)->execute($task, 'user', $cleaner->id, $admin);
        $task->refresh();
        $this->assertSame(Task::STATUS_ASSIGNED, $task->status);

        $transitioner = app(TransitionTaskStatus::class);
        $transitioner->transition($task, Task::STATUS_ACCEPTED, $cleaner);
        $transitioner->transition($task, Task::STATUS_IN_PROGRESS, $cleaner);
        $transitioner->transition($task, Task::STATUS_COMPLETED, $cleaner);
        $transitioner->transition($task, Task::STATUS_SUBMITTED_FOR_APPROVAL, $cleaner);
        $transitioner->transition($task, Task::STATUS_APPROVED, $admin);

        $task->refresh();
        $this->assertSame(Task::STATUS_APPROVED, $task->status);
        $this->assertNotNull($task->approved_at);
        $this->assertSame(5, $task->history()->count()); // machine transitions; assignment status change is a side-effect
    }

    public function test_cleaner_cannot_approve_own_task(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $task = Task::create([
            'title' => 'Self approve',
            'status' => Task::STATUS_SUBMITTED_FOR_APPROVAL,
            'approval_required' => true,
            'scheduled_start_at' => now(),
            'scheduled_end_at' => now()->addHour(),
        ]);

        app(AssignTask::class)->execute($task, 'user', $cleaner->id, $admin);

        $this->expectException(\DomainException::class);
        app(TransitionTaskStatus::class)->transition($task, Task::STATUS_APPROVED, $cleaner);
    }

    public function test_cleaner_cannot_act_on_unassigned_task(): void
    {
        $other = $this->cleaner();
        $task = Task::create([
            'title' => 'Not mine',
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => now(),
            'scheduled_end_at' => now()->addHour(),
        ]);

        $this->expectException(\DomainException::class);
        app(TransitionTaskStatus::class)->transition($task, Task::STATUS_ACCEPTED, $other);
    }

    public function test_conflict_warning_on_overlapping_assignment(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();

        $first = Task::create([
            'title' => 'First',
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => now()->addDay()->setTime(8, 0),
            'scheduled_end_at' => now()->addDay()->setTime(10, 0),
        ]);
        app(AssignTask::class)->execute($first, 'user', $cleaner->id, $admin);

        $second = Task::create([
            'title' => 'Second',
            'status' => Task::STATUS_SCHEDULED,
            'scheduled_start_at' => now()->addDay()->setTime(9, 0),
            'scheduled_end_at' => now()->addDay()->setTime(11, 0),
        ]);

        $result = app(AssignTask::class)->execute($second, 'user', $cleaner->id, $admin);

        $this->assertNotEmpty($result['warnings']);
        $this->assertSame(Task::STATUS_SCHEDULED, $second->fresh()->status);

        // Override with recorded reason assigns anyway.
        $result = app(AssignTask::class)->execute($second, 'user', $cleaner->id, $admin, true, 'Coverage needed');
        $this->assertEmpty($result['warnings']);
        $this->assertSame(Task::STATUS_ASSIGNED, $second->fresh()->status);
    }

    public function test_recurrence_generation_is_idempotent(): void
    {
        $admin = $this->admin();
        $recurrence = TaskRecurrence::create([
            'rule' => 'FREQ=WEEKLY;INTERVAL=1',
            'start_date' => now()->toDateString(),
            'time' => '08:00',
            'task_type_id' => $this->taskType()->id,
            'property_id' => $this->property()->id,
            'active' => true,
            'created_by' => $admin->id,
        ]);

        $generator = app(GenerateRecurringTasks::class);

        $first = $generator->generate($recurrence, 30, $admin);
        $second = $generator->generate($recurrence, 30, $admin);

        $this->assertGreaterThan(0, $first);
        $this->assertSame(0, $second); // no duplicates
        $this->assertSame($first, Task::where('recurrence_rule', 'FREQ=WEEKLY;INTERVAL=1')->count());
    }

    public function test_completed_instances_untouched_by_new_generation(): void
    {
        $admin = $this->admin();
        $recurrence = TaskRecurrence::create([
            'rule' => 'FREQ=DAILY;INTERVAL=1',
            'start_date' => now()->toDateString(),
            'time' => '08:00',
            'property_id' => $this->property()->id,
            'active' => true,
            'created_by' => $admin->id,
        ]);

        $generator = app(GenerateRecurringTasks::class);
        $generator->generate($recurrence, 10, $admin);

        $firstTask = Task::orderBy('scheduled_start_at')->first();
        $firstTask->update(['status' => Task::STATUS_APPROVED, 'title' => 'Completed instance']);

        $countBefore = Task::count();
        $generator->generate($recurrence, 10, $admin);

        $this->assertSame($countBefore, Task::count());
        $this->assertSame('Completed instance', Task::find($firstTask->id)->title);
    }

    public function test_api_me_tasks_and_transition(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $task = Task::create([
            'title' => 'My task',
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => now()->addDay(),
            'scheduled_end_at' => now()->addDay()->addHour(),
        ]);
        app(AssignTask::class)->execute($task, 'user', $cleaner->id, $admin);

        $token = $cleaner->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'My task');

        $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/transition", ['status' => 'accepted'])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        // Cannot act on a task that is not assigned to you.
        $other = $this->cleaner();
        Auth::forgetGuards();
        $otherToken = $other->createToken('test')->plainTextToken;
        $this->withToken($otherToken)->postJson("/api/v1/tasks/{$task->id}/transition", ['status' => 'start'])
            ->assertStatus(422);
    }

    public function test_web_list_and_filter(): void
    {
        $admin = $this->admin();
        Task::create(['title' => 'Alpha', 'status' => Task::STATUS_SCHEDULED, 'scheduled_start_at' => now()->addDay(), 'scheduled_end_at' => now()->addDay()->addHour()]);
        Task::create(['title' => 'Beta', 'status' => Task::STATUS_CANCELLED, 'scheduled_start_at' => now()->addDay(), 'scheduled_end_at' => now()->addDay()->addHour()]);

        $this->actingAs($admin)->get(route('tasks').'?tab=all')->assertSee('Alpha')->assertSee('Beta');
        $this->actingAs($admin)->get(route('tasks').'?tab=filters&status=cancelled')->assertSee('Beta')->assertDontSee('Alpha');
    }

    public function test_cleaner_cannot_create_task(): void
    {
        $cleaner = $this->cleaner();

        $this->actingAs($cleaner)->post(route('tasks.store'), $this->taskData())->assertForbidden();
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_notification_read_state(): void
    {
        $cleaner = $this->cleaner();
        $notification = Notification::create([
            'user_id' => $cleaner->id,
            'type' => 'test',
            'title' => 'Hello',
            'body' => 'World',
            'idempotency_key' => 'unique-1',
        ]);

        $this->actingAs($cleaner)->post(route('notifications.read', $notification))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);

        // Unread tab filters read items out; read feed (lazy tab) serves them.
        $this->actingAs($cleaner)->get(route('notifications'))->assertDontSee('Hello');
        $this->actingAs($cleaner)->get(route('notifications.read-feed'))->assertSee('Hello');
    }
}
