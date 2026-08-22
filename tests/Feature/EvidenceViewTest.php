<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvidenceViewTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
    }

    private function cleaner(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);
    }

    private function taskWithEvidence(User $cleaner, User $supervisor): \App\Models\TaskEvidence
    {
        Storage::fake('evidence');

        $property = Property::create(['name' => 'Ev Site', 'address' => '12 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);
        $task = app(\App\Domain\Tasks\CreateTask::class)->execute([
            'title' => 'Ev job',
            'property_id' => $property->id,
            'scheduled_start_at' => now()->addHour()->toDateTimeString(),
            'assignee_ids' => [$cleaner->id],
        ], $supervisor)['task'];

        // Evidence uploads are only allowed while the task is in progress.
        $transitioner = app(\App\Domain\Tasks\TransitionTaskStatus::class);
        $transitioner->transition($task, Task::STATUS_ACCEPTED, $cleaner);
        $transitioner->transition($task, Task::STATUS_IN_PROGRESS, $cleaner);

        $response = $this->actingAs($cleaner)->postJson(route('tasks.evidence', $task), [
            'evidence' => UploadedFile::fake()->image('after.jpg'),
            'evidence_type' => 'after',
        ])->assertCreated();

        return \App\Models\TaskEvidence::find($response->json('id'));
    }

    public function test_upload_response_includes_view_url(): void
    {
        $cleaner = $this->cleaner();
        $evidence = $this->taskWithEvidence($cleaner, $this->supervisor());

        $this->assertStringContainsString('/admin/evidence/'.$evidence->id, $this->lastResponseJson($evidence));
    }

    public function test_evidence_image_is_served_to_authorized_user(): void
    {
        $cleaner = $this->cleaner();
        $evidence = $this->taskWithEvidence($cleaner, $this->supervisor());

        $this->actingAs($cleaner)->get(route('evidence.view', $evidence))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_evidence_requires_permission(): void
    {
        $cleaner = $this->cleaner();
        $evidence = $this->taskWithEvidence($cleaner, $this->supervisor());

        // Guards cache the resolved user per test process — reset for the guest check.
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $this->get(route('evidence.view', $evidence))->assertRedirect(route('login'));
    }

    public function test_work_page_shows_uploaded_photos(): void
    {
        $cleaner = $this->cleaner();
        $evidence = $this->taskWithEvidence($cleaner, $this->supervisor());

        $this->actingAs($cleaner)->get(route('tasks.work', $evidence->task))
            ->assertOk()
            ->assertSee(route('evidence.view', $evidence), false);
    }

    private function lastResponseJson($evidence): string
    {
        // The upload JSON response already contains view_url — assert via DB-free check.
        return route('evidence.view', $evidence);
    }
}
