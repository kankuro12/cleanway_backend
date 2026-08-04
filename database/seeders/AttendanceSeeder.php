<?php

namespace Database\Seeders;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $cleaner = User::where('role', User::ROLE_CLEANER)->first();
        $supervisor = User::where('role', User::ROLE_SUPERVISOR)->first();

        if (! $cleaner) {
            return;
        }

        Shift::create([
            'user_id' => $cleaner->id,
            'date' => today()->toDateString(),
            'scheduled_start_at' => today()->setTime(8, 0),
            'scheduled_end_at' => today()->setTime(16, 0),
            'manager_id' => $supervisor?->id,
            'status' => Shift::STATUS_CONFIRMED,
        ]);

        Shift::create([
            'user_id' => $cleaner->id,
            'date' => today()->addDay()->toDateString(),
            'scheduled_start_at' => today()->addDay()->setTime(8, 0),
            'scheduled_end_at' => today()->addDay()->setTime(16, 0),
            'manager_id' => $supervisor?->id,
            'status' => Shift::STATUS_SCHEDULED,
        ]);
    }
}
