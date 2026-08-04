<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PersonnelModuleTest extends TestCase
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

    public function test_admin_can_list_and_create_personnel(): void
    {
        $admin = $this->admin();
        $branch = Branch::create(['name' => 'HQ']);

        $this->actingAs($admin)->get(route('personnel'))->assertOk();

        $this->actingAs($admin)->post(route('personnel.store'), [
            'name' => 'New Cleaner',
            'email' => 'new@cleanway.local',
            'password' => 'password123',
            'role' => User::ROLE_CLEANER,
            'status' => User::STATUS_ACTIVE,
            'branch_id' => $branch->id,
        ])->assertRedirect(route('personnel'));

        $this->assertDatabaseHas('users', [
            'email' => 'new@cleanway.local',
            'role' => User::ROLE_CLEANER,
            'branch_id' => $branch->id,
        ]);
    }

    public function test_supervisor_sees_only_scope_users(): void
    {
        $branchA = Branch::create(['name' => 'A']);
        $branchB = Branch::create(['name' => 'B']);

        $supervisor = $this->supervisor();
        $supervisor->update(['branch_id' => $branchA->id]);

        User::factory()->create(['role' => User::ROLE_CLEANER, 'branch_id' => $branchA->id]);
        $other = User::factory()->create(['role' => User::ROLE_CLEANER, 'branch_id' => $branchB->id]);
        $managed = User::factory()->create(['role' => User::ROLE_CLEANER, 'manager_id' => $supervisor->id, 'branch_id' => $branchB->id]);

        $response = $this->actingAs($supervisor)->get(route('personnel'));
        $response->assertOk();
        $response->assertSee($managed->email)
            ->assertDontSee($other->email);
    }

    public function test_cleaner_cannot_access_personnel_list(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $other = User::factory()->create(['role' => User::ROLE_CLEANER]);

        // Cleaner has no 2.1 grant — personnel list is supervisor/admin only.
        $this->actingAs($cleaner)->get(route('personnel'))->assertForbidden();

        // Own scope is visible through the profile endpoint instead.
        $token = $cleaner->createToken('test')->plainTextToken;
        $this->withToken($token)->getJson('/api/v1/me')->assertJsonPath('data.id', $cleaner->id);
    }

    public function test_cleaner_cannot_create_personnel(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($cleaner)->post(route('personnel.store'), [
            'name' => 'X',
            'email' => 'x@cleanway.local',
            'password' => 'password123',
            'role' => User::ROLE_CLEANER,
            'status' => User::STATUS_ACTIVE,
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'x@cleanway.local']);
    }

    public function test_admin_can_archive_personnel(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($admin)->delete(route('personnel.destroy', $target))->assertRedirect(route('personnel'));

        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'status' => User::STATUS_ARCHIVED]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete(route('personnel.destroy', $admin))->assertSessionHasErrors('user');
        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }

    public function test_validation_rejects_duplicate_email(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'dup@cleanway.local']);

        $this->actingAs($admin)->post(route('personnel.store'), [
            'name' => 'Dup',
            'email' => 'dup@cleanway.local',
            'password' => 'password123',
            'role' => User::ROLE_CLEANER,
            'status' => User::STATUS_ACTIVE,
        ])->assertSessionHasErrors('email');
    }

    public function test_team_membership_add_and_remove(): void
    {
        $admin = $this->admin();
        $team = Team::create(['name' => 'Alpha']);
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $this->actingAs($admin)->post(route('teams.members.store', $team), [
            'user_id' => $cleaner->id,
        ])->assertRedirect(route('teams'));

        $this->assertDatabaseHas('team_members', ['team_id' => $team->id, 'user_id' => $cleaner->id]);

        $this->actingAs($admin)->delete(route('teams.members.destroy', [$team, $cleaner]))->assertRedirect(route('teams'));
        $this->assertDatabaseMissing('team_members', ['team_id' => $team->id, 'user_id' => $cleaner->id]);
    }

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk()->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'role']]]);
    }

    public function test_api_personnel_requires_permission(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $token = $cleaner->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/personnel')->assertForbidden();
    }

    public function test_api_personnel_scoped_for_supervisor(): void
    {
        $branch = Branch::create(['name' => 'HQ']);
        $supervisor = User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'branch_id' => $branch->id]);
        $token = $supervisor->createToken('test')->plainTextToken;

        $inScope = User::factory()->create(['role' => User::ROLE_CLEANER, 'branch_id' => $branch->id]);
        $outScope = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $response = $this->withToken($token)->getJson('/api/v1/personnel')->assertOk();
        $data = $response->json('data');

        collect($data)->each(function ($row) use ($outScope): void {
            $this->assertNotEquals($outScope->id, $row['id']);
        });
        $this->assertContains($inScope->id, array_column($data, 'id'));
    }

    public function test_api_me_returns_own_profile(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $token = $cleaner->createToken('test')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $cleaner->id);
    }

    public function test_api_logout_revokes_token(): void
    {
        $cleaner = User::factory()->create(['role' => User::ROLE_CLEANER]);
        $token = $cleaner->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        // Guards cache the resolved user per test process — reset between requests.
        Auth::forgetGuards();

        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }
}
