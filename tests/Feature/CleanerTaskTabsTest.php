<?php

namespace Tests\Feature;

use App\Domain\Tasks\CreateTask;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanerTaskTabsTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
    }

    private function property(): Property
    {
        return Property::create(['name' => 'Tab Site', 'address' => '11 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);
    }

    private function makeTask(User $cleaner, User $supervisor, string $title, string $status, string $when): Task
    {
        $task = app(CreateTask::class)->execute([
            'title' => $title,
            'property_id' => $this->property()->id,
            'scheduled_start_at' => $when,
            'assignee_ids' => [$cleaner->id],
        ], $supervisor)['task'];

        $task->update(['status' => $status]);

        return $task;
    }

    public function test_cleaner_sees_current_and_finished_tabs(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->makeTask($cleaner, $supervisor, 'Active job', Task::STATUS_IN_PROGRESS, now()->addDay()->setTime(8, 0)->toDateTimeString());
        $this->makeTask($cleaner, $supervisor, 'Done job', Task::STATUS_APPROVED, now()->addDay()->setTime(9, 0)->toDateTimeString());
        $this->makeTask($cleaner, $supervisor, 'Rejected job', Task::STATUS_REJECTED, now()->addDay()->setTime(10, 0)->toDateTimeString());

        $response = $this->actingAs($cleaner)->get(route('tasks'));
        $response->assertOk();
        $response->assertSee('Current tasks')->assertSee('Finished tasks');
        $response->assertSee('Active job')->assertDontSee('Done job')->assertDontSee('Rejected job');

        $response = $this->actingAs($cleaner)->get(route('tasks', ['tab' => 'finished']));
        $response->assertSee('Done job')->assertSee('Rejected job')->assertDontSee('Active job');
    }

    public function test_cleaner_list_has_no_filter_bar_and_asc_order(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $later = $this->makeTask($cleaner, $supervisor, 'Later task', Task::STATUS_ASSIGNED, now()->addDays(3)->setTime(9, 0)->toDateTimeString());
        $earlier = $this->makeTask($cleaner, $supervisor, 'Earlier task', Task::STATUS_ASSIGNED, now()->addDay()->setTime(8, 0)->toDateTimeString());

        $response = $this->actingAs($cleaner)->get(route('tasks'));
        $response->assertOk();

        // No filter controls for cleaners.
        $response->assertDontSee('All statuses', false);
        $response->assertDontSee('All priorities', false);

        // Earliest scheduled first.
        $html = $response->getContent();
        $this->assertTrue(strpos($html, 'Earlier task') < strpos($html, 'Later task'));
        $this->assertNotNull($earlier->id);
        $this->assertNotNull($later->id);
    }

    public function test_supervisor_still_gets_management_list(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->makeTask($cleaner, $supervisor, 'Team job', Task::STATUS_ASSIGNED, now()->addDay()->toDateTimeString());

        $response = $this->actingAs($supervisor)->get(route('tasks'));
        $response->assertOk();
        $response->assertSee('All statuses', false); // supervisor keeps filters
        $response->assertDontSee('Current tasks');
    }
}
