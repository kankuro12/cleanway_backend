<?php

namespace Tests\Feature;

use App\Mail\TaskApprovalRequestedMail;
use App\Mail\TaskAssignedMail;
use App\Models\NotificationDelivery;
use App\Models\Property;
use App\Models\Task;
use App\Models\TaskSubtask;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CleanerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE, 'email' => 'sup@cleanway.local']);
    }

    private function cleaner(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);
    }

    private function property(): Property
    {
        return Property::create([
            'name' => 'Work Site',
            'address' => '6 Test Rd',
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
            'geocode_status' => Property::GEOCODE_RESOLVED,
            'permitted_check_in_radius_meters' => 100,
        ]);
    }

    private function taskType(): TaskType
    {
        return TaskType::create([
            'name' => 'Field Job Type',
            'slug' => 'field-job-type',
            'before_photo_required' => false,
            'after_photo_required' => false,
            'minimum_photo_count' => 0,
            'approval_required' => false,
            'active' => true,
        ]);
    }

    private function assignedTask(User $cleaner, User $supervisor, array $extra = []): Task
    {
        $result = app(\App\Domain\Tasks\CreateTask::class)->execute(array_merge([
            'title' => 'Field job',
            'task_type_id' => $this->taskType()->id,
            'property_id' => $this->property()->id,
            'scheduled_start_at' => now()->addHour()->toDateTimeString(),
            'assignee_ids' => [$cleaner->id],
            'assigned_manager_id' => $supervisor->id,
            'subtasks' => [['title' => 'Check the bins']],
        ], $extra), $supervisor);

        return $result['task'];
    }

    private function startTask(Task $task, User $cleaner): void
    {
        $transitioner = app(\App\Domain\Tasks\TransitionTaskStatus::class);
        $transitioner->transition($task, Task::STATUS_ACCEPTED, $cleaner);
        $transitioner->transition($task, Task::STATUS_IN_PROGRESS, $cleaner);
    }

    private function fulfillRequirements(Task $task, User $cleaner): void
    {
        foreach ($task->checklistSnapshot as $item) {
            if ($item->completed_at === null) {
                $item->update(['completed_at' => now(), 'completed_by' => $cleaner->id]);
            }
        }
    }

    public function test_cleaner_can_view_work_page(): void
    {
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());

        $this->actingAs($cleaner)->get(route('tasks.work', $task))
            ->assertOk()
            ->assertSee('Field job')
            ->assertSee('Check the bins')
            ->assertSee('Punch in & start', false);
    }

    public function test_work_page_shows_task_detail_not_edit_controls(): void
    {
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());

        $response = $this->actingAs($cleaner)->get(route('tasks.work', $task));
        $response->assertOk();
        $response->assertDontSee('Save schedule & details');
        $response->assertDontSee('Move status');
    }

    public function test_punch_in_inside_geofence_starts_work(): void
    {
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());

        $response = $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), [
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
            'gps_accuracy_meters' => 8,
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('inside_geofence'));
        $this->assertSame('in_progress', $response->json('task_status'));
        $this->assertDatabaseHas('attendance_events', ['user_id' => $cleaner->id, 'event_type' => 'clock_in', 'inside_geofence' => 1]);

        // Punch payload: time saved + geofence validation details.
        $punch = $response->json('punch');
        $this->assertNotNull($punch['punched_in_at']);
        $this->assertNotNull($punch['id']);
        $this->assertSame(0, (int) $punch['distance_meters']);
        $this->assertSame(100, $punch['radius_meters']);
        $this->assertTrue($punch['inside_geofence']);
        $this->assertDatabaseHas('attendance_events', ['task_id' => $task->id, 'user_id' => $cleaner->id]);
    }

    public function test_punch_in_outside_geofence_blocks(): void
    {
        config(['gps.out_of_radius_policy' => 'override']);

        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());

        $response = $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), [
            'latitude' => -36.8700,
            'longitude' => 174.7800,
            'gps_accuracy_meters' => 10,
        ]);

        $response->assertStatus(403);
        $this->assertTrue($response->json('blocked'));
        $this->assertSame('assigned', $response->json('task_status'));

        // Recorded punch includes why it was unsuccessful.
        $punch = $response->json('punch');
        $this->assertNotNull($punch['punched_in_at']);
        $this->assertFalse($punch['inside_geofence']);
        $this->assertGreaterThan($punch['radius_meters'], $punch['distance_meters']);
        $this->assertNotEmpty($punch['reason']);
        $this->assertDatabaseHas('attendance_events', ['task_id' => $task->id, 'inside_geofence' => 0]);
    }

    public function test_punch_in_when_geofence_disabled_starts_work_even_if_outside(): void
    {
        config(['gps.geofence_enforced' => false]);
        config(['gps.out_of_radius_policy' => 'override']);

        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());

        $response = $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), [
            'latitude' => -36.8700,
            'longitude' => 174.7800,
            'gps_accuracy_meters' => 10,
        ]);

        $response->assertOk();
        $this->assertFalse($response->json('blocked'));
        $this->assertSame('Work started.', $response->json('message'));
        $this->assertSame('in_progress', $response->json('task_status'));
        $this->assertDatabaseHas('attendance_events', ['task_id' => $task->id, 'inside_geofence' => 1]);
    }

    public function test_punch_in_without_coordinates_starts_work_when_geofence_disabled(): void
    {
        config(['gps.geofence_enforced' => false]);

        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());

        $response = $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), []);

        $response->assertOk();
        $this->assertFalse($response->json('blocked'));
        $this->assertSame('Work started.', $response->json('message'));
        $this->assertSame('in_progress', $response->json('task_status'));
    }

    public function test_subtask_tick_ajax(): void
    {
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());
        $subtask = $task->subtasks()->first();

        $response = $this->actingAs($cleaner)->postJson(route('tasks.subtasks.toggle', [$task, $subtask]));

        $response->assertOk()->assertJsonPath('completed', true);
        $this->assertNotNull($subtask->fresh()->completed_at);
    }

    public function test_evidence_upload_ajax_with_preview_flow(): void
    {
        Storage::fake('evidence');

        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor());
        $this->startTask($task, $cleaner);

        $response = $this->actingAs($cleaner)->postJson(route('tasks.evidence', $task), [
            'evidence' => UploadedFile::fake()->image('after.jpg'),
            'evidence_type' => 'after',
        ]);

        $response->assertCreated()->assertJsonPath('evidence_type', 'after');
        $this->assertDatabaseHas('task_evidence', ['task_id' => $task->id, 'evidence_type' => 'after']);
    }

    public function test_complete_with_approval_notifies_supervisor_by_mail_and_inapp(): void
    {
        Mail::fake();
        Queue::fake();

        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $supervisor, ['approval_required' => true]);

        // Punch in + start.
        $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), [
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
        ])->assertOk();
        $this->fulfillRequirements($task, $cleaner);

        // Complete → auto submit for approval.
        $response = $this->actingAs($cleaner)->postJson(route('tasks.complete', $task), ['remarks' => 'All done']);
        $response->assertOk();
        $this->assertSame('submitted_for_approval', $response->json('task_status'));

        Mail::assertQueued(TaskApprovalRequestedMail::class, 1);
        Mail::assertQueued(TaskApprovalRequestedMail::class, fn (TaskApprovalRequestedMail $mail) => $mail->hasTo('sup@cleanway.local'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $supervisor->id,
            'type' => 'task.submitted_for_approval',
        ]);
        $this->assertDatabaseHas('notification_deliveries', ['channel' => 'email', 'status' => 'sent']);
    }

    public function test_complete_without_approval_just_completes(): void
    {
        Mail::fake();

        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $this->supervisor(), ['approval_required' => false]);

        $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), [
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
        ])->assertOk();
        $this->fulfillRequirements($task, $cleaner);

        $response = $this->actingAs($cleaner)->postJson(route('tasks.complete', $task), ['remarks' => 'Done']);
        $response->assertOk();
        $this->assertSame('completed', $response->json('task_status'));

        Mail::assertNotQueued(TaskApprovalRequestedMail::class);
    }

    public function test_completion_gate_blocks_missing_evidence(): void
    {
        Mail::fake();

        $cleaner = $this->cleaner();
        $photoType = TaskType::create([
            'name' => 'Photo Job',
            'slug' => 'photo-job',
            'before_photo_required' => true,
            'after_photo_required' => true,
            'minimum_photo_count' => 2,
            'approval_required' => true,
            'active' => true,
        ]);
        $task = $this->assignedTask($cleaner, $this->supervisor(), ['task_type_id' => $photoType->id]);

        $this->actingAs($cleaner)->postJson(route('tasks.work-checkin', $task), [
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
        ])->assertOk();

        $response = $this->actingAs($cleaner)->postJson(route('tasks.complete', $task), ['remarks' => 'x']);
        $response->assertStatus(422);
        $this->assertNotEmpty($response->json('missing'));
    }
}
