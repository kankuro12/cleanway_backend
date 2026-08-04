<?php

namespace Tests\Feature;

use App\Mail\TaskAssignedMail;
use App\Models\NotificationDelivery;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TaskAssignedMailTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
    }

    public function test_mail_sent_to_each_assignee_on_assignment(): void
    {
        Mail::fake();

        $supervisor = $this->supervisor();
        $cleanerA = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE, 'email' => 'a@cleanway.local']);
        $cleanerB = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE, 'email' => 'b@cleanway.local']);
        $property = Property::create(['name' => 'Mail Site', 'address' => '3 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);

        $this->actingAs($supervisor)->post(route('tasks.store'), [
            'property_id' => $property->id,
            'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
            'assignee_ids' => [$cleanerA->id, $cleanerB->id],
            'subtasks' => [['title' => 'Bins']],
        ])->assertRedirect();

        $task = Task::first();

        Mail::assertQueued(TaskAssignedMail::class, 2);
        Mail::assertQueued(TaskAssignedMail::class, fn (TaskAssignedMail $mail) => $mail->hasTo('a@cleanway.local') && $mail->task->is($task));
        Mail::assertQueued(TaskAssignedMail::class, fn (TaskAssignedMail $mail) => $mail->hasTo('b@cleanway.local'));
    }

    public function test_email_delivery_rows_logged(): void
    {
        Mail::fake();

        $supervisor = $this->supervisor();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);
        $property = Property::create(['name' => 'Row Site', 'address' => '4 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);

        $this->actingAs($supervisor)->post(route('tasks.store'), [
            'property_id' => $property->id,
            'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
            'assignee_ids' => [$cleaner->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('notification_deliveries', [
            'channel' => NotificationDelivery::CHANNEL_EMAIL,
            'status' => NotificationDelivery::STATUS_SENT,
        ]);
        $this->assertDatabaseHas('notification_deliveries', [
            'channel' => NotificationDelivery::CHANNEL_IN_APP,
            'status' => NotificationDelivery::STATUS_SENT,
        ]);
    }

    public function test_team_assignment_sends_no_email(): void
    {
        Mail::fake();

        $supervisor = $this->supervisor();
        $team = \App\Models\Team::create(['name' => 'Night Crew']);
        $property = Property::create(['name' => 'Team Site', 'address' => '5 Test Rd', 'geocode_status' => Property::GEOCODE_RESOLVED]);

        $this->actingAs($supervisor)->post(route('tasks.store'), [
            'property_id' => $property->id,
            'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
            'team_id' => $team->id,
        ])->assertRedirect();

        Mail::assertNothingSent();
    }
}
