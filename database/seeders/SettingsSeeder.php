<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $org = [
            'default_check_in_radius_meters' => ['150', 'Fallback level 3 of the check-in radius chain (property → category → here → 150 m system fallback).'],
            'gps_out_of_radius_policy' => ['exception', 'Out-of-radius policy: accept | exception | override | reject.'],
            'gps_missing_coordinates_policy' => ['override', 'Missing-coordinate policy: accept | exception | override | reject.'],
        ];

        $system = [
            'geofence_enforced' => ['0', 'Enable Geofence Distance Validation. When disabled, GPS coordinates are recorded if available without gating or requiring supervisor approval.'],
            'gps_max_accuracy_meters' => ['50', 'Warn/reject when reported GPS accuracy exceeds this.'],
            'gps_require_checkout' => ['0', 'Require a GPS check-out event before task completion.'],
            'task_require_completion_remarks' => ['1', 'Require remarks on task completion.'],
            'task_require_incident_ack' => ['0', 'Require open incidents to be acknowledged before completion.'],
        ];

        foreach ($org as $key => [$value, $description]) {
            Setting::updateOrCreate(
                ['scope' => Setting::SCOPE_ORGANIZATION, 'key' => $key],
                ['value' => $value, 'description' => $description]
            );
        }

        foreach ($system as $key => [$value, $description]) {
            Setting::updateOrCreate(
                ['scope' => Setting::SCOPE_SYSTEM, 'key' => $key],
                ['value' => $value, 'description' => $description]
            );
        }
    }
}
