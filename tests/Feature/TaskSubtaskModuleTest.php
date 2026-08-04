<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Task;
use App\Models\TaskSubtask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskSubtaskModuleTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
    }

    private function property(): Property
    {
        return Property::create(['name' => 'Site B', 'address' => '2 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);
    }

    private function taskData(array $overrides = []): array
    {
        return array_merge([
            'property_id' => $this->property()->id,
            'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
        ], $overrides);
    }

    public function test_title_auto_derived_from_property(): void
    {
        $supervisor = $this->supervisor();

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData())->assertRedirect();

        $task = Task::first();
        $this->assertSame('Site B', $task->title);
        $this->assertSame('Site B', $task->property_name_snapshot);
    }

    public function test_multiple_assignees_created(): void
    {
        $supervisor = $this->supervisor();
        $cleanerA = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);
        $cleanerB = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData([
            'assignee_ids' => [$cleanerA->id, $cleanerB->id],
        ]))->assertRedirect();

        $task = Task::first();
        $this->assertSame(2, $task->assignments()->where('assignee_type', 'user')->count());
        $this->assertSame(Task::STATUS_ASSIGNED, $task->status);
        $this->assertDatabaseHas('notifications', ['user_id' => $cleanerA->id, 'type' => 'task.assigned']);
        $this->assertDatabaseHas('notifications', ['user_id' => $cleanerB->id, 'type' => 'task.assigned']);
    }

    public function test_subtasks_created_with_task(): void
    {
        $supervisor = $this->supervisor();

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData([
            'subtasks' => [
                ['title' => 'Wipe glass'],
                ['title' => 'Clean toilets'],
            ],
        ]))->assertRedirect();

        $task = Task::first();
        $this->assertSame(2, $task->subtasks()->count());
        $this->assertDatabaseHas('task_subtasks', ['task_id' => $task->id, 'title' => 'Wipe glass', 'sort_order' => 0]);
        $this->assertDatabaseHas('task_subtasks', ['task_id' => $task->id, 'title' => 'Clean toilets', 'sort_order' => 1]);
    }

    public function test_subtask_toggle_completion(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData([
            'assignee_ids' => [$cleaner->id],
            'subtasks' => [['title' => 'Door check']],
        ]));

        $task = Task::first();
        $subtask = $task->subtasks()->first();

        $this->actingAs($cleaner)->post(route('tasks.subtasks.toggle', [$task, $subtask]))->assertRedirect();
        $this->assertNotNull($subtask->fresh()->completed_at);

        $this->actingAs($cleaner)->post(route('tasks.subtasks.toggle', [$task, $subtask]))->assertRedirect();
        $this->assertNull($subtask->fresh()->completed_at);
    }

    public function test_add_subtask_from_edit_page(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData(['assignee_ids' => [$cleaner->id]]));
        $task = Task::first();

        // Cleaner (4.4) can add sub tasks.
        $this->actingAs($cleaner)->post(route('tasks.subtasks.store', $task), ['title' => 'Extra check'])->assertRedirect();
        $this->assertDatabaseHas('task_subtasks', ['task_id' => $task->id, 'title' => 'Extra check']);
    }

    public function test_api_me_tasks_includes_subtasks(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($supervisor)->post(route('tasks.store'), $this->taskData([
            'assignee_ids' => [$cleaner->id],
            'subtasks' => [['title' => 'Bin check']],
        ]));

        // Guards cache the resolved user per test process — reset between requests.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $token = $cleaner->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me/tasks')
            ->assertOk()
            ->assertJsonPath('data.0.subtasks.0.title', 'Bin check');
    }
}
