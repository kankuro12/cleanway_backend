<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::firstOrCreate(['name' => 'Head Office'], ['address' => '1 Main Street']);

        $supervisor = User::factory()->create([
            'name' => 'Test Supervisor',
            'email' => 'supervisor@cleanway.local',
            'role' => User::ROLE_SUPERVISOR,
            'branch_id' => $branch->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $cleaner = User::factory()->create([
            'name' => 'Test Cleaner',
            'email' => 'cleaner@cleanway.local',
            'role' => User::ROLE_CLEANER,
            'branch_id' => $branch->id,
            'manager_id' => $supervisor->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $team = Team::firstOrCreate(
            ['name' => 'Alpha Crew'],
            ['branch_id' => $branch->id, 'lead_id' => $supervisor->id],
        );

        $team->members()->syncWithoutDetaching([
            $cleaner->id => ['role_in_team' => 'cleaner', 'joined_at' => now()],
        ]);
    }
}
