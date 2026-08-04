<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_route_requires_no_auth_or_permission(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_any_protected_route(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/settings/users')->assertOk();
    }

    public function test_supervisor_can_access_own_grants_but_not_root_permission(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);

        $this->actingAs($supervisor)->get('/admin/dashboard')->assertOk();
        $this->actingAs($supervisor)->get('/admin/personnel')->assertOk();
        $this->actingAs($supervisor)->get('/admin/settings')->assertForbidden();
    }

    public function test_cleaner_is_rejected_from_supervisor_routes(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($cleaner)->get('/admin/personnel')->assertForbidden();
        $this->actingAs($cleaner)->get('/admin/settings')->assertForbidden();
    }

    public function test_granting_parent_implies_sub_permissions(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->assertTrue($admin->hasPermission('1.1'));

        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $this->assertFalse($cleaner->hasPermission('1.1'));
        $this->assertFalse($cleaner->hasPermission('1')); // sub grant does not imply parent
        $this->assertTrue($cleaner->hasPermission('3.1'));
    }

    public function test_multi_permission_middleware_allows_any_role_holding_one(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]); // has 4.1, not 7.1

        $this->actingAs($admin)->get('/admin/reports')->assertOk();
        $this->actingAs($cleaner)->get('/admin/dashboard')->assertOk();
        $this->actingAs($cleaner)->get('/admin/reports')->assertOk(); // cleaner lacks 7.1, 4.1 granted
    }

    public function test_group_level_permission_protects_every_child(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($cleaner)->get('/admin/properties/create')->assertOk(); // cleaner has 3.1
        $this->actingAs($cleaner)->get('/admin/settings/users')->assertForbidden();
    }

    public function test_role_middleware_accepts_multiple_roles(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]);
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($supervisor)->get('/admin/cleaner-tools')->assertOk();
        $this->actingAs($cleaner)->get('/admin/cleaner-tools')->assertOk();
        $this->actingAs($admin)->get('/admin/cleaner-tools')->assertForbidden();
    }

    public function test_permission_and_role_combined_on_one_route(): void
    {
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR]); // has 4.5 + role 1
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]); // no 4.5, role 2
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]); // has permission, but role 0 fails

        $this->actingAs($supervisor)->get('/supervisor-only-approvals')->assertOk();
        $this->actingAs($cleaner)->get('/supervisor-only-approvals')->assertForbidden();
        $this->actingAs($admin)->get('/supervisor-only-approvals')->assertForbidden(); // AND: role must also pass
    }
}
