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

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@cleanway.local'],
            [
                'name' => 'Test Supervisor',
                'password' => bcrypt('password'),
                'role' => User::ROLE_SUPERVISOR,
                'branch_id' => $branch->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );

        $cleanerIds = [];

        // Legacy test compatibility cleaner
        $cleanerLegacy = User::firstOrCreate(
            ['email' => 'cleaner@cleanway.local'],
            [
                'name' => 'Cleaner 1',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CLEANER,
                'branch_id' => $branch->id,
                'manager_id' => $supervisor->id,
                'status' => User::STATUS_ACTIVE,
            ]
        );
        $cleanerIds[$cleanerLegacy->id] = ['role_in_team' => 'cleaner', 'joined_at' => now()];

        for ($i = 1; $i <= 22; $i++) {
            $email = "cleaner{$i}@cleanway.local";
            $cleaner = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Cleaner {$i}",
                    'password' => bcrypt('password'),
                    'role' => User::ROLE_CLEANER,
                    'branch_id' => $branch->id,
                    'manager_id' => $supervisor->id,
                    'status' => User::STATUS_ACTIVE,
                ]
            );
            $cleanerIds[$cleaner->id] = ['role_in_team' => 'cleaner', 'joined_at' => now()];
        }

        $team = Team::firstOrCreate(
            ['name' => 'Alpha Crew'],
            ['branch_id' => $branch->id, 'lead_id' => $supervisor->id],
        );

        $team->members()->syncWithoutDetaching($cleanerIds);
    }
}
