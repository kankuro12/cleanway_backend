<?php

namespace Tests\Feature;

use App\Commands\ManageUsersCommand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

class ManageUsersCommandTest extends TestCase
{
    use RefreshDatabase;

    private function tester(): CommandTester
    {
        $application = new \Symfony\Component\Console\Application;
        $command = new ManageUsersCommand;
        $command->setLaravel($this->app);
        $application->addCommand($command);

        return new CommandTester($application->find('users:manage'));
    }

    public function test_add_user_flow(): void
    {
        $tester = $this->tester();
        $tester->setInputs(['Add user', 'Joe Cleaner', 'joe@cleanway.local', 'password123', '2 = Cleaner', 'active', 'Quit']);
        $tester->execute(['command' => 'users:manage']);

        $tester->assertCommandIsSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'joe@cleanway.local', 'name' => 'Joe Cleaner', 'role' => User::ROLE_CLEANER, 'status' => 'active']);
    }

    public function test_edit_user_flow(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $tester = $this->tester();
        $tester->setInputs([
            'Edit user',
            "#{$user->id} {$user->name} <{$user->email}>",
            'Renamed Worker',
            $user->email,
            'inactive',
            'Quit',
        ]);
        $tester->execute(['command' => 'users:manage']);

        $tester->assertCommandIsSuccessful();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Renamed Worker', 'status' => 'inactive']);
    }

    public function test_change_role_flow(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $tester = $this->tester();
        $tester->setInputs([
            'Change role',
            "#{$user->id} {$user->name} <{$user->email}>",
            '1 = Supervisor',
            'Quit',
        ]);
        $tester->execute(['command' => 'users:manage']);

        $tester->assertCommandIsSuccessful();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => User::ROLE_SUPERVISOR]);
    }

    public function test_change_password_flow(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CLEANER, 'password' => 'oldpass123']);

        $tester = $this->tester();
        $tester->setInputs([
            'Change password',
            "#{$user->id} {$user->name} <{$user->email}>",
            'newpass456',
            'Quit',
        ]);
        $tester->execute(['command' => 'users:manage']);

        $tester->assertCommandIsSuccessful();
        $this->assertTrue(Hash::check('newpass456', $user->fresh()->password));
        $this->assertFalse(Hash::check('oldpass123', $user->fresh()->password));
    }

    public function test_rejects_duplicate_email_on_add(): void
    {
        User::factory()->create(['email' => 'taken@cleanway.local']);

        $tester = $this->tester();
        $tester->setInputs(['Add user', 'Dup', 'taken@cleanway.local', 'password123', '2 = Cleaner', 'active', 'Quit']);
        $tester->execute(['command' => 'users:manage']);

        $this->assertStringContainsString('The email has already been taken', $tester->getDisplay());
        $this->assertSame(1, User::count());
    }

    public function test_list_users_flow(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_CLEANER]);

        $tester = $this->tester();
        $tester->setInputs(['List users', 'Quit']);
        $tester->execute(['command' => 'users:manage']);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString($user->email, $tester->getDisplay());
    }
}
