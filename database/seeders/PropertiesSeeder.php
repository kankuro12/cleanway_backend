<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Models\PropertyCategory;
use App\Models\PropertyTag;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertiesSeeder extends Seeder
{
    public function run(): void
    {
        $office = PropertyCategory::create([
            'name' => 'Office',
            'slug' => PropertyCategory::uniqueSlug('Office'),
            'description' => 'Commercial office spaces',
            'default_check_in_radius_meters' => 100,
            'default_safety_instructions' => 'Check in with reception on arrival.',
            'active' => true,
            'sort_order' => 10,
        ]);

        $retail = PropertyCategory::create([
            'name' => 'Retail',
            'slug' => PropertyCategory::uniqueSlug('Retail'),
            'description' => 'Shops and retail premises',
            'default_check_in_radius_meters' => 75,
            'active' => true,
            'sort_order' => 20,
        ]);

        $keyAccount = PropertyTag::create(['name' => 'Key Account', 'slug' => 'key-account', 'color' => '#f59e0b', 'active' => true, 'sort_order' => 10]);
        $afterHours = PropertyTag::create(['name' => 'After Hours', 'slug' => 'after-hours', 'color' => '#6366f1', 'active' => true, 'sort_order' => 20]);
        $hazard = PropertyTag::create(['name' => 'Hazard Aware', 'slug' => 'hazard-aware', 'color' => '#ef4444', 'active' => true, 'sort_order' => 30]);

        $supervisor = User::where('role', User::ROLE_SUPERVISOR)->first();
        $cleaner = User::where('role', User::ROLE_CLEANER)->first();

        $samples = [
            [
                'name' => 'Harbourview Offices',
                'address' => '1 Queen Street, Auckland CBD, Auckland 1010',
                'formatted_address' => '1 Queen Street, Auckland CBD, Auckland 1010, New Zealand',
                'latitude' => -36.8431487,
                'longitude' => 174.7653813,
                'geocode_accuracy' => 'rooftop',
                'geocode_status' => Property::GEOCODE_RESOLVED,
                'geocoded_at' => now(),
                'location_source' => Property::SOURCE_GOOGLE_PLACES,
                'property_category_id' => $office->id,
                'contact_name' => 'Sarah Mitchell',
                'contact_phone' => '+64 9 555 0142',
                'contact_email' => 'sarah@harbourview.example',
                'postal_code' => '1010',
                'access_instructions' => 'Reception on ground floor; after-hours code 4821.',
                'service_frequency' => 'weekly',
                'tags' => [$keyAccount->id, $afterHours->id],
            ],
            [
                'name' => 'Northland Mall Kiosk',
                'address' => '12 Queen Street, Auckland CBD, Auckland 1010',
                'formatted_address' => '12 Queen Street, Auckland CBD, Auckland 1010, New Zealand',
                'latitude' => -36.8443000,
                'longitude' => 174.7665000,
                'geocode_accuracy' => 'rooftop',
                'geocode_status' => Property::GEOCODE_RESOLVED,
                'geocoded_at' => now(),
                'location_source' => Property::SOURCE_GOOGLE_GEOCODING,
                'property_category_id' => $retail->id,
                'permitted_check_in_radius_meters' => 50,
                'contact_name' => 'Mike Chen',
                'contact_phone' => '+64 9 555 0188',
                'service_frequency' => 'daily',
                'tags' => [$hazard->id],
            ],
            [
                'name' => 'Riverside Depot (pending)',
                'address' => '77 Riverside Road, Parnell, Auckland 1052',
                'geocode_status' => Property::GEOCODE_PENDING,
                'location_source' => Property::SOURCE_UNKNOWN,
                'service_frequency' => 'fortnightly',
                'tags' => [],
            ],
        ];

        $branch = \App\Models\Branch::first();

        foreach ($samples as $index => $sample) {
            $tags = $sample['tags'] ?? [];
            unset($sample['tags']);

            $property = Property::create($sample + [
                'created_by' => $supervisor?->id,
                'active' => true,
            ]);

            $property->tags()->sync($tags);

            if ($supervisor) {
                PropertyAssignment::create([
                    'property_id' => $property->id,
                    'assignable_type' => 'user',
                    'assignable_id' => $supervisor->id,
                    'assignment_role' => PropertyAssignment::ROLE_SUPERVISOR,
                    'is_primary' => true,
                    'assigned_by' => $supervisor->id,
                    'start_date' => now()->subMonths(2)->toDateString(),
                ]);
            }

            if ($cleaner && $index === 0) {
                PropertyAssignment::create([
                    'property_id' => $property->id,
                    'assignable_type' => 'user',
                    'assignable_id' => $cleaner->id,
                    'assignment_role' => PropertyAssignment::ROLE_CLEANER,
                    'is_primary' => true,
                    'assigned_by' => $supervisor?->id,
                    'start_date' => now()->subMonths(1)->toDateString(),
                ]);
            }

            if ($branch) {
                PropertyAssignment::create([
                    'property_id' => $property->id,
                    'assignable_type' => 'branch',
                    'assignable_id' => $branch->id,
                    'assignment_role' => PropertyAssignment::ROLE_BRANCH,
                    'assigned_by' => $supervisor?->id,
                ]);
            }
        }
    }
}
