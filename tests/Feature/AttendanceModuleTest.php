<?php

namespace Tests\Feature;

use App\Domain\Attendance\SubmitAttendanceCorrection;
use App\Domain\Tasks\AssignTask;
use App\Domain\Tasks\CompleteTask;
use App\Domain\Tasks\CreateTask;
use App\Domain\Tasks\TransitionTaskStatus;
use App\Domain\Tasks\UploadTaskEvidence;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\GpsException;
use App\Models\Property;
use App\Models\Shift;
use App\Models\Task;
use App\Models\TaskChecklistResponse;
use App\Models\TaskEvidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceModuleTest extends TestCase
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

    private function property(): Property
    {
        return Property::create([
            'name' => 'GPS Site',
            'address' => '1 Test Rd',
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
            'geocode_status' => Property::GEOCODE_RESOLVED,
            'permitted_check_in_radius_meters' => 100,
        ]);
    }

    private function assignedTask(User $cleaner, User $supervisor): Task
    {
        $result = app(CreateTask::class)->execute([
            'title' => 'GPS task',
            'property_id' => $this->property()->id,
            'scheduled_start_at' => now()->addHour()->toDateTimeString(),
            'assignee_type' => 'user',
            'assignee_id' => $cleaner->id,
        ], $supervisor);

        return $result['task'];
    }

    public function test_e2e_checkin_complete_submit_approve(): void
    {
        Storage::fake('evidence');

        $admin = $this->admin();
        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $supervisor);

        $token = $cleaner->createToken('test')->plainTextToken;

        // Check-in inside radius.
        $response = $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/check-in", [
            'latitude' => -36.8484597,
            'longitude' => 174.7633315,
            'gps_accuracy_meters' => 8,
            'device_timestamp' => now()->toIso8601String(),
        ]);
        $response->assertOk();
        $this->assertTrue($response->json('data.inside_geofence'));

        // Start via transition.
        $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/transition", ['status' => 'start'])->assertOk();

        // Evidence (before + after).
        $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/evidence", [
            'evidence' => UploadedFile::fake()->image('before.jpg'),
            'evidence_type' => 'before',
        ])->assertCreated();

        $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/evidence", [
            'evidence' => UploadedFile::fake()->image('after.jpg'),
            'evidence_type' => 'after',
        ])->assertCreated();

        // Complete with remarks.
        $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/complete", [
            'responses' => [],
            'remarks' => 'Job done, keys returned.',
        ])->assertOk()->assertJsonPath('data.status', 'completed');

        // Submit + approve.
        $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/transition", ['status' => 'submit'])->assertOk();

        $this->actingAs($admin)->post(route('approvals.decide', $task), ['action' => 'approve'])->assertRedirect();

        $task->refresh();
        $this->assertSame(Task::STATUS_APPROVED, $task->status);
        $this->assertDatabaseHas('task_approvals', ['task_id' => $task->id, 'action' => 'approve']);
        $this->assertDatabaseCount('attendance_events', 1);
    }

    public function test_checkin_out_of_radius_creates_exception(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $supervisor);
        $token = $cleaner->createToken('test')->plainTextToken;

        // 2 km away.
        $response = $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/check-in", [
            'latitude' => -36.8700,
            'longitude' => 174.7800,
            'gps_accuracy_meters' => 10,
        ]);

        $response->assertOk(); // policy = exception → allowed, exception recorded
        $this->assertFalse($response->json('data.inside_geofence'));
        $this->assertDatabaseHas('gps_exceptions', ['policy' => GpsException::POLICY_EXCEPTION]);
    }

    public function test_checkin_override_policy_blocks(): void
    {
        config(['gps.out_of_radius_policy' => 'override']);

        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();
        $task = $this->assignedTask($cleaner, $supervisor);
        $token = $cleaner->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/check-in", [
            'latitude' => -36.8700,
            'longitude' => 174.7800,
            'gps_accuracy_meters' => 10,
        ]);

        $response->assertStatus(403);
        $this->assertTrue($response->json('data.blocked'));
        $this->assertDatabaseHas('gps_exceptions', ['policy' => GpsException::POLICY_OVERRIDE]);
        $this->assertSame(Task::STATUS_ASSIGNED, $task->fresh()->status);
    }

    public function test_checkin_missing_coordinates_no_crash(): void
    {
        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();
        $property = Property::create(['name' => 'No coords', 'address' => '2 Test Rd', 'geocode_status' => Property::GEOCODE_FAILED]);

        $result = app(CreateTask::class)->execute([
            'title' => 'No GPS',
            'property_id' => $property->id,
            'scheduled_start_at' => now()->addHour()->toDateTimeString(),
            'assignee_type' => 'user',
            'assignee_id' => $cleaner->id,
        ], $supervisor);

        $token = $cleaner->createToken('test')->plainTextToken;

        // Policy = override (default): event recorded, check-in blocked until manager approval.
        $response = $this->withToken($token)->postJson("/api/v1/tasks/{$result['task']->id}/check-in", [
            'latitude' => -36.8,
            'longitude' => 174.7,
        ]);

        $response->assertStatus(403);
        $this->assertTrue($response->json('data.blocked'));
        $this->assertDatabaseHas('gps_exceptions', ['reason' => 'Property has no resolved coordinates — GPS verification unavailable.', 'policy' => GpsException::POLICY_OVERRIDE]);
    }

    public function test_attendance_clock_rules_and_summary(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $token = $cleaner->createToken('test')->plainTextToken;

        $shift = Shift::create([
            'user_id' => $cleaner->id,
            'date' => today()->toDateString(),
            'scheduled_start_at' => now()->subHour()->toDateTimeString(),
            'scheduled_end_at' => now()->addHours(3)->toDateTimeString(),
            'status' => Shift::STATUS_CONFIRMED,
        ]);

        $this->withToken($token)->postJson('/api/v1/attendance/clock-in', ['shift_id' => $shift->id])->assertCreated();
        $this->withToken($token)->postJson('/api/v1/attendance/break/start', ['shift_id' => $shift->id])->assertCreated();
        $this->withToken($token)->postJson('/api/v1/attendance/break/end', ['shift_id' => $shift->id])->assertCreated();
        $this->withToken($token)->postJson('/api/v1/attendance/clock-out', ['shift_id' => $shift->id])->assertCreated();

        $this->withToken($token)->getJson('/api/v1/me/shifts')->assertOk()
            ->assertJsonPath('data.0.summary.late', true)
            ->assertJsonStructure(['data' => [['summary' => ['worked_minutes', 'break_minutes', 'overtime_minutes']]]]);

        $this->assertDatabaseCount('attendance_events', 4);
    }

    public function test_correction_preserves_original_event(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $shift = Shift::create([
            'user_id' => $cleaner->id,
            'date' => today()->toDateString(),
            'scheduled_start_at' => now()->setTime(8, 0),
            'scheduled_end_at' => now()->setTime(16, 0),
            'status' => Shift::STATUS_CONFIRMED,
        ]);

        $event = AttendanceEvent::create([
            'user_id' => $cleaner->id,
            'shift_id' => $shift->id,
            'event_type' => AttendanceEvent::TYPE_CLOCK_IN,
            'server_timestamp' => now(),
        ]);

        // Original is immutable.
        $this->expectException(\LogicException::class);
        $event->update(['remarks' => 'hack']);
    }

    public function test_correction_request_and_approval(): void
    {
        $admin = $this->admin();
        $cleaner = $this->cleaner();
        $shift = Shift::create([
            'user_id' => $cleaner->id,
            'date' => today()->toDateString(),
            'scheduled_start_at' => now()->setTime(8, 0),
            'scheduled_end_at' => now()->setTime(16, 0),
            'status' => Shift::STATUS_CONFIRMED,
        ]);

        $event = AttendanceEvent::create([
            'user_id' => $cleaner->id,
            'shift_id' => $shift->id,
            'event_type' => AttendanceEvent::TYPE_CLOCK_IN,
            'server_timestamp' => now()->setTime(9, 30),
        ]);

        $service = app(SubmitAttendanceCorrection::class);
        $request = $service->request($cleaner, $event, 'Traffic delay — actual start 8:45');
        $service->decide($request, AttendanceCorrectionRequest::DECISION_APPROVED, $admin, ['server_timestamp' => now()->setTime(8, 45)], 'Approved');

        // New manual_correction event; original untouched.
        $this->assertDatabaseHas('attendance_events', ['event_type' => AttendanceEvent::TYPE_MANUAL_CORRECTION]);
        $this->assertSame('09:30', $event->fresh()->server_timestamp->format('H:i'));
        $this->assertDatabaseHas('attendance_correction_requests', ['id' => $request->id, 'decision' => 'approved']);
    }

    public function test_completion_gate_blocks_missing_photos(): void
    {
        Storage::fake('evidence');

        $admin = $this->admin();
        $supervisor = $this->supervisor();
        $cleaner = $this->cleaner();

        $result = app(CreateTask::class)->execute([
            'title' => 'Photo task',
            'task_type_id' => \App\Models\TaskType::create([
                'name' => 'Photo Task',
                'slug' => 'photo-task',
                'before_photo_required' => true,
                'after_photo_required' => true,
                'minimum_photo_count' => 2,
                'approval_required' => true,
                'active' => true,
            ])->id,
            'property_id' => $this->property()->id,
            'scheduled_start_at' => now()->addHour()->toDateTimeString(),
            'assignee_type' => 'user',
            'assignee_id' => $cleaner->id,
        ], $supervisor);

        $task = $result['task'];
        $transitioner = app(TransitionTaskStatus::class);
        $transitioner->transition($task, Task::STATUS_ACCEPTED, $cleaner);
        $transitioner->transition($task, Task::STATUS_IN_PROGRESS, $cleaner);

        $gate = app(CompleteTask::class);

        // Only one after photo → blocked.
        app(UploadTaskEvidence::class)->execute($task, $cleaner, UploadedFile::fake()->image('a.jpg'), 'after');

        $result = $gate->execute($task, $cleaner, [], 'done');
        $this->assertFalse($result['ok']);
        $this->assertNotEmpty($result['missing']);
        $this->assertStringContainsString('before photo', implode(' ', $result['missing']));
        $this->assertSame(Task::STATUS_IN_PROGRESS, $task->fresh()->status);

        // Both photos → passes.
        app(UploadTaskEvidence::class)->execute($task, $cleaner, UploadedFile::fake()->image('b.jpg'), 'before');
        app(UploadTaskEvidence::class)->execute($task, $cleaner, UploadedFile::fake()->image('c.jpg'), 'after');

        $result = $gate->execute($task, $cleaner, [], 'done');
        $this->assertTrue($result['ok']);
        $this->assertSame(Task::STATUS_COMPLETED, $task->fresh()->status);
    }

    public function test_incident_raise_and_transition(): void
    {
        $cleaner = $this->cleaner();
        $admin = $this->admin();
        $token = $cleaner->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/tasks/99999/incidents', [
            'category' => 'safety_hazard',
            'severity' => 'high',
            'description' => 'Spilled chemical in hallway.',
        ])->assertNotFound();

        $task = $this->assignedTask($cleaner, $this->supervisor());

        $response = $this->withToken($token)->postJson("/api/v1/tasks/{$task->id}/incidents", [
            'category' => 'safety_hazard',
            'severity' => 'high',
            'description' => 'Spilled chemical in hallway.',
        ])->assertCreated();

        $incident = \App\Models\Incident::find($response->json('data.id'));
        $this->assertSame('open', $incident->status);

        $this->actingAs($admin)->post(route('incidents.transition', $incident), [
            'status' => 'resolved',
            'resolution' => 'Area cleaned, hazard removed.',
        ])->assertRedirect();

        $this->assertSame('resolved', $incident->fresh()->status);
        $this->assertNotNull($incident->fresh()->resolved_at);
    }
}
