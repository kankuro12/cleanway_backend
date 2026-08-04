<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionOverridesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_grant_override_adds_permission(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->assertFalse($cleaner->hasPermission('4.5'));

        UserPermission::create(['user_id' => $cleaner->id, 'permission' => '4.5', 'granted' => true]);

        $this->assertTrue($cleaner->fresh()->hasPermission('4.5'));
        $this->assertTrue($cleaner->fresh()->hasAnyPermission(['4.5']));
    }

    public function test_parent_grant_covers_children(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        UserPermission::create(['user_id' => $cleaner->id, 'permission' => '4', 'granted' => true]);

        $this->assertTrue($cleaner->fresh()->hasPermission('4.2'));
        $this->assertTrue($cleaner->fresh()->hasPermission('4.8'));
    }

    public function test_deny_override_blocks_role_permission(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->assertTrue($cleaner->hasPermission('3.1')); // role baseline grants 3.1

        UserPermission::create(['user_id' => $cleaner->id, 'permission' => '3.1', 'granted' => false]);

        $this->assertFalse($cleaner->fresh()->hasPermission('3.1'));
        $this->assertFalse($cleaner->fresh()->hasAnyPermission(['3.1']));
    }

    public function test_deny_parent_blocks_children(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        UserPermission::create(['user_id' => $cleaner->id, 'permission' => '4', 'granted' => false]);

        $this->assertFalse($cleaner->fresh()->hasPermission('4.1'));
    }

    public function test_most_specific_override_wins(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        UserPermission::create(['user_id' => $cleaner->id, 'permission' => '4', 'granted' => false]);
        UserPermission::create(['user_id' => $cleaner->id, 'permission' => '4.4', 'granted' => true]);

        $this->assertFalse($cleaner->fresh()->hasPermission('4.1')); // parent deny
        $this->assertTrue($cleaner->fresh()->hasPermission('4.4'));  // specific grant wins
    }

    public function test_cleaner_edit_page_redirects_to_work_page(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $property = Property::create(['name' => 'P', 'address' => 'A', 'geocode_status' => Property::GEOCODE_RESOLVED]);
        $task = app(\App\Domain\Tasks\CreateTask::class)->execute([
            'title' => 'Edit redirect',
            'property_id' => $property->id,
            'scheduled_start_at' => now()->addDay()->toDateTimeString(),
            'assignee_ids' => [$cleaner->id],
        ], User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]))['task'];

        $this->actingAs($cleaner)->get(route('tasks.edit', $task))
            ->assertRedirect(route('tasks.work', $task));

        // Supervisor (4.3) still gets the edit page.
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'status' => User::STATUS_ACTIVE]);
        $this->actingAs($supervisor)->get(route('tasks.edit', $task))->assertOk();
    }

    public function test_permission_page_admin_only_and_update(): void
    {
        $admin = $this->admin();
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($cleaner)->get(route('permissions'))->assertForbidden();

        $this->actingAs($admin)->get(route('permissions', ['user_id' => $cleaner->id]))
            ->assertOk()
            ->assertSee('Permission fine-tuning')
            ->assertSee('permissions[4.5]');

        $this->actingAs($admin)->post(route('permissions.update', $cleaner), [
            'permissions' => ['4.5' => 'grant', '3.1' => 'deny'],
        ])->assertRedirect();

        $this->assertDatabaseHas('user_permissions', ['user_id' => $cleaner->id, 'permission' => '4.5', 'granted' => 1]);
        $this->assertDatabaseHas('user_permissions', ['user_id' => $cleaner->id, 'permission' => '3.1', 'granted' => 0]);

        $this->assertTrue($cleaner->fresh()->hasPermission('4.5'));
        $this->assertFalse($cleaner->fresh()->hasPermission('3.1'));
    }
}
