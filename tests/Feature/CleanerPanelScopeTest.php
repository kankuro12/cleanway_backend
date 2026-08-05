<?php

namespace Tests\Feature;

use App\Domain\Tasks\CreateTask;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanerPanelScopeTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
    }

    private function property(): Property
    {
        return Property::create(['name' => 'Scope Site', 'address' => '9 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);
    }

    private function taskFor(User $cleaner, User $supervisor, string $title): Task
    {
        return app(CreateTask::class)->execute([
            'title' => $title,
            'property_id' => $this->property()->id,
            'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
            'assignee_ids' => [$cleaner->id],
        ], $supervisor)['task'];
    }

    public function test_cleaner_sees_only_own_tasks_in_list(): void
    {
        $supervisor = $this->supervisor();
        $cleanerA = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $cleanerB = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $myTask = $this->taskFor($cleanerA, $supervisor, 'Mine only');
        $this->taskFor($cleanerB, $supervisor, 'Not mine');

        $response = $this->actingAs($cleanerA)->get(route('tasks.my'));
        $response->assertOk()->assertSee('Mine only')->assertDontSee('Not mine');

        $this->assertSame(1, $response->viewData('current')->total());
    }

    public function test_supervisor_still_sees_all_tasks(): void
    {
        $supervisor = $this->supervisor();
        $cleanerA = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $cleanerB = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->taskFor($cleanerA, $supervisor, 'Task A');
        $this->taskFor($cleanerB, $supervisor, 'Task B');

        $response = $this->actingAs($supervisor)->get(route('tasks'));
        $response->assertOk()->assertSee('Task A')->assertSee('Task B');
    }

    public function test_cleaner_calendar_events_only_own_tasks(): void
    {
        $supervisor = $this->supervisor();
        $cleanerA = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $cleanerB = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->taskFor($cleanerA, $supervisor, 'Cal Mine');
        $this->taskFor($cleanerB, $supervisor, 'Cal Not mine');

        $response = $this->actingAs($cleanerA)->getJson(route('calendar.events').'?from='.now()->toDateString().'&to='.now()->addDays(2)->toDateString());
        $response->assertOk();

        $titles = array_column($response->json('data'), 'title');
        $this->assertContains('Cal Mine', $titles);
        $this->assertNotContains('Cal Not mine', $titles);
    }

    public function test_cleaner_sidebar_has_no_properties_link(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $response = $this->actingAs($cleaner)->get(route('dashboard'));
        $response->assertOk();
        $response->assertDontSee('href="http://localhost:8000/admin/properties"', false);
        $response->assertSee('Tasks', false);
    }

    public function test_cleaner_cannot_open_all_tasks_list(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($cleaner)->get(route('tasks'))->assertForbidden();
    }

    public function test_cleaner_dashboard_works(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($cleaner)->get(route('dashboard'))->assertOk();
    }
}
