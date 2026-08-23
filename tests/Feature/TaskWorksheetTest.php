<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Property;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskWorksheetTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function cleaner(string $name = 'Cleaner Bob'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => User::ROLE_CLEANER,
            'email_verified_at' => now(),
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_authenticated_user_can_view_worksheet(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $type = TaskType::create(['name' => 'General Clean', 'slug' => 'general-clean', 'color' => '#0284c7', 'active' => true]);
        $client = Client::create(['name' => 'Skyline Properties', 'active' => true]);
        $property = Property::create([
            'client_id' => $client->id,
            'name' => 'Seaside Villa',
            'address' => '100 Ocean Drive',
            'active' => true,
        ]);

        $task = Task::create([
            'title' => 'Villa Turnover Clean',
            'task_type_id' => $type->id,
            'property_id' => $property->id,
            'status' => Task::STATUS_IN_PROGRESS,
            'scheduled_start_at' => today()->setHour(9)->setMinute(0),
            'scheduled_end_at' => today()->setHour(12)->setMinute(0),
            'estimated_duration_minutes' => 180,
            'worked_seconds' => 3600,
        ]);

        TaskAssignment::create([
            'task_id' => $task->id,
            'assignee_type' => 'user',
            'assignee_id' => $cleaner->id,
            'assignment_role' => 'cleaner',
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.worksheet'));

        $response->assertOk();
        $response->assertSee('Task Work Sheet');
        $response->assertSee('Seaside Villa');
        $response->assertSee('Cleaner Bob');
        $response->assertSee('01:00:00');
    }

    public function test_worksheet_filters_by_date_range(): void
    {
        $admin = $this->admin();
        $type = TaskType::create(['name' => 'General Clean', 'slug' => 'general-clean', 'active' => true]);
        $propertyToday = Property::create(['name' => 'Downtown Today Apt', 'address' => '1 Main St', 'active' => true]);
        $propertyFuture = Property::create(['name' => 'Future Next Week Apt', 'address' => '2 Elm St', 'active' => true]);

        $todayTask = Task::create([
            'title' => 'Today Task',
            'task_type_id' => $type->id,
            'property_id' => $propertyToday->id,
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => today()->setHour(10),
        ]);

        $nextWeekTask = Task::create([
            'title' => 'Future Next Week Task',
            'task_type_id' => $type->id,
            'property_id' => $propertyFuture->id,
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => today()->addDays(7)->setHour(10),
        ]);

        // Filter for today only
        $response = $this->actingAs($admin)->get(route('tasks.worksheet', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertSee('Downtown Today Apt');
        $response->assertDontSee('Future Next Week Apt');

        // Filter for next week
        $response2 = $this->actingAs($admin)->get(route('tasks.worksheet', [
            'start_date' => today()->addDays(6)->toDateString(),
            'end_date' => today()->addDays(8)->toDateString(),
        ]));

        $response2->assertOk();
        $response2->assertSee('Future Next Week Apt');
        $response2->assertDontSee('Downtown Today Apt');
    }

    public function test_worksheet_filters_by_personnel(): void
    {
        $admin = $this->admin();
        $cleaner1 = $this->cleaner('Alice Clean');
        $cleaner2 = $this->cleaner('Bob Clean');

        $type = TaskType::create(['name' => 'General Clean', 'slug' => 'general-clean', 'active' => true]);
        $property1 = Property::create(['name' => 'Alice Suburban Home', 'address' => '20 Elm St', 'active' => true]);
        $property2 = Property::create(['name' => 'Bob Suburban Home', 'address' => '30 Elm St', 'active' => true]);

        $task1 = Task::create([
            'title' => 'Alice Assigned Job',
            'task_type_id' => $type->id,
            'property_id' => $property1->id,
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => today()->setHour(10),
        ]);
        TaskAssignment::create([
            'task_id' => $task1->id,
            'assignee_type' => 'user',
            'assignee_id' => $cleaner1->id,
            'assignment_role' => 'cleaner',
        ]);

        $task2 = Task::create([
            'title' => 'Bob Assigned Job',
            'task_type_id' => $type->id,
            'property_id' => $property2->id,
            'status' => Task::STATUS_ASSIGNED,
            'scheduled_start_at' => today()->setHour(11),
        ]);
        TaskAssignment::create([
            'task_id' => $task2->id,
            'assignee_type' => 'user',
            'assignee_id' => $cleaner2->id,
            'assignment_role' => 'cleaner',
        ]);

        // Filter for Alice only
        $response = $this->actingAs($admin)->get(route('tasks.worksheet', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'personnel_ids' => [$cleaner1->id],
        ]));

        $response->assertOk();
        $response->assertSee('Alice Suburban Home');
        $response->assertSee('Alice Clean');
        $response->assertDontSee('Bob Suburban Home');
    }
}
