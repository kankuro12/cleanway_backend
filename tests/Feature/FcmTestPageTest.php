<?php

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use App\Models\Team;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FcmTestPageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_page_admin_only(): void
    {
        $admin = $this->admin();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->get(route('fcm-test'))->assertRedirect(route('login'));
        $this->actingAs($cleaner)->get(route('fcm-test'))->assertForbidden();
        $this->actingAs($admin)->get(route('fcm-test'))->assertOk()->assertSee('Push message test');
    }

    public function test_send_to_single_user(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER]);
        UserDevice::create(['user_id' => $target->id, 'fcm_token' => 't-1', 'platform' => 'web']);

        $this->actingAs($admin)->post(route('fcm-test.send'), [
            'recipient' => 'user',
            'user_id' => $target->id,
            'title' => 'Hello there',
            'body' => 'Test body',
        ])->assertRedirect(route('fcm-test'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $target->id,
            'type' => 'fcm.test',
            'title' => 'Hello there',
        ]);

        $delivery = NotificationDelivery::where('channel', NotificationDelivery::CHANNEL_PUSH)->first();
        $this->assertNotNull($delivery);
        $this->assertSame(NotificationDelivery::STATUS_PENDING, $delivery->status);
        Queue::assertPushed(\App\Jobs\SendPushNotification::class);
    }

    public function test_send_to_role_group(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $cleanerA = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $cleanerB = User::factory()->create(['role' => User::ROLE_CLEANER]);
        UserDevice::create(['user_id' => $cleanerA->id, 'fcm_token' => 't-a', 'platform' => 'web']);

        $this->actingAs($admin)->post(route('fcm-test.send'), [
            'recipient' => 'role',
            'role' => 2,
            'title' => 'All cleaners',
            'body' => '',
        ])->assertRedirect(route('fcm-test'));

        $this->assertSame(2, \App\Models\Notification::where('type', 'fcm.test')->count());
        // One user has a device → 1 push pending, 1 skipped.
        $this->assertSame(1, NotificationDelivery::where('channel', 'push')->where('status', 'pending')->count());
        $this->assertSame(1, NotificationDelivery::where('channel', 'push')->where('status', 'skipped')->count());
    }

    public function test_send_to_team_group(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $team = Team::create(['name' => 'Crew A']);
        $member = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $team->members()->attach($member->id);

        $this->actingAs($admin)->post(route('fcm-test.send'), [
            'recipient' => 'team',
            'team_id' => $team->id,
            'title' => 'Team message',
            'body' => '',
        ])->assertRedirect(route('fcm-test'));

        $this->assertDatabaseHas('notifications', ['user_id' => $member->id, 'type' => 'fcm.test', 'title' => 'Team message']);
    }

    public function test_ajax_send_returns_json_without_redirect(): void
    {
        Queue::fake();

        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($admin)->postJson(route('fcm-test.send'), [
            'recipient' => 'user',
            'user_id' => $target->id,
            'title' => 'Ajax push',
            'body' => 'No reload',
        ])->assertOk()
            ->assertJsonPath('message', 'Sent to 1 user(s): 0 with FCM push, 1 without devices.');
    }
}
