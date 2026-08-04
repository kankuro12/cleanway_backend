<?php

namespace Tests\Feature;

use App\Jobs\SendPushNotification;
use App\Models\NotificationDelivery;
use App\Models\User;
use App\Models\UserDevice;
use App\Services\Notifications\FirebaseMessenger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FirebasePushTest extends TestCase
{
    use RefreshDatabase;

    private function cleaner(): User
    {
        return User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);
    }

    public function test_device_registration_api(): void
    {
        $cleaner = $this->cleaner();
        $token = $cleaner->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/me/devices', [
            'fcm_token' => 'fcm-token-abc-123',
            'platform' => 'web',
        ])->assertCreated();

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $cleaner->id,
            'fcm_token' => 'fcm-token-abc-123',
            'platform' => 'web',
        ]);

        // Same token re-registered by another user → ownership moves.
        $other = $this->cleaner();
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $otherToken = $other->createToken('test')->plainTextToken;

        $this->withToken($otherToken)->postJson('/api/v1/me/devices', ['fcm_token' => 'fcm-token-abc-123'])
            ->assertCreated();

        $this->assertSame(1, UserDevice::where('fcm_token', 'fcm-token-abc-123')->where('user_id', $other->id)->count());

        // Logout path: delete own device.
        $this->withToken($otherToken)->deleteJson('/api/v1/me/devices/fcm-token-abc-123')->assertOk();
        $this->assertDatabaseMissing('user_devices', ['fcm_token' => 'fcm-token-abc-123']);
    }

    public function test_push_channel_dispatches_job_per_device(): void
    {
        Queue::fake();

        $cleaner = $this->cleaner();
        UserDevice::create(['user_id' => $cleaner->id, 'fcm_token' => 'dev-1', 'platform' => 'web']);
        UserDevice::create(['user_id' => $cleaner->id, 'fcm_token' => 'dev-2', 'platform' => 'android']);

        app(\App\Services\Notifications\NotificationService::class)->send(
            $cleaner,
            'task.assigned',
            'New task assigned',
            'Body text',
            ['task_id' => 1],
            'key-1',
            [NotificationDelivery::CHANNEL_IN_APP, NotificationDelivery::CHANNEL_PUSH],
        );

        Queue::assertPushed(SendPushNotification::class, 2);

        $this->assertDatabaseHas('notification_deliveries', ['channel' => 'push', 'status' => 'pending']);
        $this->assertSame(2, NotificationDelivery::where('channel', 'push')->count());
    }

    public function test_push_skipped_when_no_devices(): void
    {
        Queue::fake();

        $cleaner = $this->cleaner();

        app(\App\Services\Notifications\NotificationService::class)->send(
            $cleaner,
            'task.assigned',
            'New task assigned',
            null,
            [],
            'key-2',
            [NotificationDelivery::CHANNEL_PUSH],
        );

        Queue::assertNothingPushed();
        $this->assertDatabaseHas('notification_deliveries', ['channel' => 'push', 'status' => 'skipped']);
    }

    public function test_push_job_marks_delivery_sent(): void
    {
        Queue::fake();

        $cleaner = $this->cleaner();
        UserDevice::create(['user_id' => $cleaner->id, 'fcm_token' => 'dev-1', 'platform' => 'web']);

        $service = app(\App\Services\Notifications\NotificationService::class);
        $service->send($cleaner, 'x', 'T', 'B', [], 'key-3', [NotificationDelivery::CHANNEL_PUSH]);

        $delivery = NotificationDelivery::where('channel', 'push')->first();

        $messenger = $this->createMock(FirebaseMessenger::class);
        $messenger->expects($this->once())->method('send')
            ->with('dev-1', 'T', 'B', [])
            ->willReturn(true);

        (new SendPushNotification($delivery->id, 'dev-1', 'T', 'B'))->handle($messenger);

        $this->assertSame(NotificationDelivery::STATUS_SENT, $delivery->fresh()->status);
        $this->assertNotNull($delivery->fresh()->delivered_at);
    }

    public function test_push_job_marks_delivery_failed_when_fcm_errors(): void
    {
        Queue::fake();

        $cleaner = $this->cleaner();
        UserDevice::create(['user_id' => $cleaner->id, 'fcm_token' => 'dev-1', 'platform' => 'web']);

        app(\App\Services\Notifications\NotificationService::class)->send($cleaner, 'x', 'T', 'B', [], 'key-4', [NotificationDelivery::CHANNEL_PUSH]);
        $delivery = NotificationDelivery::where('channel', 'push')->first();

        $messenger = $this->createMock(FirebaseMessenger::class);
        $messenger->expects($this->once())->method('send')->willReturn(false);

        (new SendPushNotification($delivery->id, 'dev-1', 'T', 'B'))->handle($messenger);

        $this->assertSame(NotificationDelivery::STATUS_FAILED, $delivery->fresh()->status);
    }

    public function test_messenger_noops_when_not_configured(): void
    {
        config(['firebase.enabled' => false]);

        $this->assertFalse(app(FirebaseMessenger::class)->send('any-token', 'T', 'B'));
    }
}
