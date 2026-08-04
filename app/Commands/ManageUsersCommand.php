<?php

namespace App\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManageUsersCommand extends Command
{
    protected $signature = 'users:manage';

    protected $description = 'Interactive user management: list, add, edit, change role, change password';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('users:manage is interactive — run it in a terminal.');

            return self::FAILURE;
        }

        $this->info('=== CleanWay user management ===');

        while (true) {
            $choice = $this->choice('What do you want to do?', [
                'List users',
                'Add user',
                'Edit user',
                'Change role',
                'Change password',
                'Quit',
            ], 0);

            try {
                match ($choice) {
                    'List users' => $this->listUsers(),
                    'Add user' => $this->addUser(),
                    'Edit user' => $this->editUser(),
                    'Change role' => $this->changeRole(),
                    'Change password' => $this->changePassword(),
                    default => $this->info('Bye.'),
                };
            } catch (\Symfony\Component\Console\Exception\MissingInputException $e) {
                $this->info('Aborted — no input.');

                return self::SUCCESS;
            } catch (\Throwable $e) {
                $this->error($e->getMessage());
            }

            if ($choice === 'Quit') {
                return self::SUCCESS;
            }
        }
    }

    private function listUsers(): void
    {
        $users = User::with(['branch:id,name', 'team:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'status', 'branch_id', 'team_id']);

        if ($users->isEmpty()) {
            $this->warn('No users found.');

            return;
        }

        $this->table(
            ['ID', 'Name', 'Email', 'Role', 'Status', 'Branch', 'Team'],
            $users->map(fn (User $user) => [
                $user->id,
                $user->name,
                $user->email,
                $this->roleLabel($user->role),
                $user->status,
                $user->branch?->name ?? '—',
                $user->team?->name ?? '—',
            ])->all()
        );
    }

    private function addUser(): void
    {
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->ask('Password', 'password');
        $role = (int) $this->choice('Role', ['0 = Admin', '1 = Supervisor', '2 = Cleaner'], 2);
        $status = $this->choice('Status', ['active', 'invited', 'inactive', 'suspended', 'on_leave'], 0);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ], compact('name', 'email', 'password'));

        DB::transaction(function () use ($data, $role, $status): void {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $role,
                'status' => $status,
            ]);
        });

        $this->info("User {$data['email']} created (role {$this->roleLabel($role)}).");
    }

    private function editUser(): void
    {
        $user = $this->selectUser('Which user do you want to edit?');

        $name = $this->ask('Name', $user->name);
        $email = $this->ask('Email', $user->email);
        $status = $this->choice('Status', ['active', 'invited', 'inactive', 'suspended', 'on_leave', 'archived'], array_search($user->status, ['active', 'invited', 'inactive', 'suspended', 'on_leave', 'archived'], true) ?: 0);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ], compact('name', 'email'));

        DB::transaction(function () use ($user, $data, $status): void {
            $user->update(['name' => $data['name'], 'email' => $data['email'], 'status' => $status]);
        });

        $this->info("User #{$user->id} updated.");
    }

    private function changeRole(): void
    {
        $user = $this->selectUser('Which user do you want to reassign?');
        $role = (int) $this->choice('New role', ['0 = Admin', '1 = Supervisor', '2 = Cleaner'], 2);

        DB::transaction(function () use ($user, $role): void {
            $user->update(['role' => $role]);
        });

        $this->info("User #{$user->id} ({$user->email}) role changed to {$this->roleLabel($role)}.");
    }

    private function changePassword(): void
    {
        $user = $this->selectUser('Which user password do you want to reset?');

        $password = $this->ask('New password');

        $data = $this->validate(['password' => ['required', 'string', 'min:8']], compact('password'));

        DB::transaction(function () use ($user, $data): void {
            $user->update(['password' => $data['password']]);
        });

        $this->info("Password reset for {$user->email}.");
    }

    private function selectUser(string $question): User
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        if ($users->isEmpty()) {
            throw new \RuntimeException('No users exist yet — add one first.');
        }

        $labels = $users->map(fn (User $user) => "#{$user->id} {$user->name} <{$user->email}>")->all();
        $label = $this->choice($question, $labels);

        preg_match('/^#(\d+)/', (string) $label, $matches);

        return $users->firstWhere('id', (int) $matches[1]);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function validate(array $rules, array $data): array
    {
        $validator = validator($data, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    private function roleLabel(int $role): string
    {
        return match ($role) {
            User::ROLE_ADMIN => 'Admin',
            User::ROLE_SUPERVISOR => 'Supervisor',
            default => 'Cleaner',
        };
    }
}
