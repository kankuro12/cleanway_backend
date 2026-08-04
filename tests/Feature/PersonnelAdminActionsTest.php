<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PersonnelAdminActionsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
    }

    public function test_admin_can_change_password(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER, 'password' => 'oldpass123']);

        $this->actingAs($admin)->post(route('personnel.password', $target), [
            'password' => 'newpass456',
            'password_confirmation' => 'newpass456',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('newpass456', $target->fresh()->password));
        $this->assertFalse(Hash::check('oldpass123', $target->fresh()->password));
    }

    public function test_password_requires_confirmation_and_min_length(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($admin)->post(route('personnel.password', $target), [
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->post(route('personnel.password', $target), [
            'password' => 'newpass456',
            'password_confirmation' => 'different456',
        ])->assertSessionHasErrors('password_confirmation');
    }

    public function test_supervisor_can_change_password_with_2_3(): void
    {
        // 2.3 (Personnel > Edit) is granted to supervisors — password reset rides it.
        $supervisor = $this->supervisor();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($supervisor)->post(route('personnel.password', $target), [
            'password' => 'newpass456',
            'password_confirmation' => 'newpass456',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('newpass456', $target->fresh()->password));
    }

    public function test_admin_can_deactivate_and_reactivate(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER, 'status' => User::STATUS_ACTIVE]);

        $this->actingAs($admin)->post(route('personnel.toggle-active', $target))->assertRedirect();
        $this->assertSame(User::STATUS_INACTIVE, $target->fresh()->status);

        $this->actingAs($admin)->post(route('personnel.toggle-active', $target))->assertRedirect();
        $this->assertSame(User::STATUS_ACTIVE, $target->fresh()->status);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('personnel.toggle-active', $admin))->assertSessionHasErrors('user');
        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);
    }

    public function test_admin_can_archive_from_list(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($admin)->delete(route('personnel.destroy', $target))->assertRedirect(route('personnel'));

        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => User::STATUS_ARCHIVED]);
    }
}
