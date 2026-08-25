<?php

namespace Database\Seeders;

use App\Models\BedType;
use App\Models\Client;
use App\Models\LinenType;
use App\Models\Property;
use App\Models\PropertyBed;
use App\Models\PropertyLinen;
use Illuminate\Database\Seeder;

class ClientAndLinenSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Bed Types (Only 3 from truth file)
        $bedTypes = [
            ['name' => 'Super King Bed', 'description' => 'Super King Bed', 'sort_order' => 1],
            ['name' => 'Queen Bed', 'description' => 'Queen Bed', 'sort_order' => 2],
            ['name' => 'Single Bed', 'description' => 'Single Bed', 'sort_order' => 3],
        ];

        foreach ($bedTypes as $bt) {
            BedType::updateOrCreate(['name' => $bt['name']], $bt);
        }

        // 2. Seed Linen Types (Only 9 from truth file)
        $linenTypes = [
            ['name' => 'Super King Sheet', 'rate' => 19.00, 'description' => 'Super King Sheet', 'sort_order' => 1],
            ['name' => 'Queen Sheet', 'rate' => 16.00, 'description' => 'Queen Sheet', 'sort_order' => 2],
            ['name' => 'Single Sheet', 'rate' => 12.00, 'description' => 'Single Sheet', 'sort_order' => 3],
            ['name' => 'Pillow Slip', 'rate' => 3.00, 'description' => 'Pillow Slip', 'sort_order' => 4],
            ['name' => 'Bath Towels', 'rate' => 4.50, 'description' => 'Bath Towels', 'sort_order' => 5],
            ['name' => 'Face cloths', 'rate' => 1.50, 'description' => 'Face cloths', 'sort_order' => 6],
            ['name' => 'Hand Towels', 'rate' => 2.50, 'description' => 'Hand Towels', 'sort_order' => 7],
            ['name' => 'Bath Matt', 'rate' => 3.50, 'description' => 'Bath Matt', 'sort_order' => 8],
            ['name' => 'Tea Towels', 'rate' => 2.00, 'description' => 'Tea Towels', 'sort_order' => 9],
        ];

        foreach ($linenTypes as $lt) {
            LinenType::updateOrCreate(['name' => $lt['name']], $lt);
        }

        // 3. Seed Clients from JSON
        $dataPath = database_path('data/initial_properties_data.json');
        $jsonData = file_exists($dataPath) ? json_decode(file_get_contents($dataPath), true) : null;

        if (! $jsonData) {
            return;
        }

        $clientMap = [];
        $clientNames = $jsonData['client_list'] ?? [
            'BRJ', 'Cleanway', 'Mega Food', 'OFS - Alisa', 'OFS - Lisa', 'Reena', 'Zodiak'
        ];

        foreach ($clientNames as $name) {
            $client = Client::firstOrCreate(
                ['name' => $name],
                [
                    'company_name' => $name,
                    'email' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)) . '@client.local',
                    'active' => true,
                ]
            );
            $clientMap[$name] = $client;
        }

        // 4. Seed Properties from JSON
        $supervisor = \App\Models\User::where('role', \App\Models\User::ROLE_SUPERVISOR)->first();
        $cleaner1 = \App\Models\User::where('role', \App\Models\User::ROLE_CLEANER)->first();
        $cleaner2 = \App\Models\User::where('role', \App\Models\User::ROLE_CLEANER)->skip(1)->first() ?? $cleaner1;

        $bedMap = [
            'super_king_bed' => BedType::firstWhere('name', 'Super King Bed')?->id,
            'queen_bed' => BedType::firstWhere('name', 'Queen Bed')?->id,
            'single_bed' => BedType::firstWhere('name', 'Single Bed')?->id,
        ];

        $linenMap = [
            'super_king_sheet' => LinenType::firstWhere('name', 'Super King Sheet')?->id,
            'queen_sheet' => LinenType::firstWhere('name', 'Queen Sheet')?->id,
            'single_sheet' => LinenType::firstWhere('name', 'Single Sheet')?->id,
            'pillow_slip' => LinenType::firstWhere('name', 'Pillow Slip')?->id,
            'bath_towels' => LinenType::firstWhere('name', 'Bath Towels')?->id,
            'face_cloths' => LinenType::firstWhere('name', 'Face cloths')?->id,
            'hand_towels' => LinenType::firstWhere('name', 'Hand Towels')?->id,
            'bath_matt' => LinenType::firstWhere('name', 'Bath Matt')?->id,
            'tea_towels' => LinenType::firstWhere('name', 'Tea Towels')?->id,
        ];

        if (! empty($jsonData['clients'])) {
            foreach ($jsonData['clients'] as $clientSection) {
                $cName = $clientSection['client_name'];
                $client = $clientMap[$cName] ?? Client::firstOrCreate(['name' => $cName], ['active' => true]);

                foreach ($clientSection['properties'] as $pIdx => $pData) {
                    $bedrooms = is_numeric($pData['bed_rooms'] ?? null) ? (int) $pData['bed_rooms'] : 1;
                    $bathrooms = is_numeric($pData['bathroom_count'] ?? null) ? (float) $pData['bathroom_count'] : 1.0;
                    $name = $pData['address'];

                    $durationMinutes = null;
                    if (isset($pData['duration_hour']) || isset($pData['duration_minute'])) {
                        $h = (int) ($pData['duration_hour'] ?? 0);
                        $m = (int) ($pData['duration_minute'] ?? 0);
                        $durationMinutes = ($h * 60) + $m;
                    }

                    $property = Property::updateOrCreate(
                        ['name' => $name, 'client_id' => $client->id],
                        [
                            'address' => $pData['address'],
                            'property_code' => $pData['property_code'] ?? null,
                            'client_id' => $client->id,
                            'bedrooms_count' => $bedrooms,
                            'bathrooms_count' => $bathrooms,
                            'cleaning_duration_minutes' => $durationMinutes ?: 120,
                            'parking_type' => ! empty($pData['parking']) && stripos($pData['parking'], 'Yes') !== false ? 'garage' : 'none',
                            'access_instructions' => ! empty($pData['area_suburb']) ? "Area/Suburb: {$pData['area_suburb']}" : null,
                            'active' => true,
                            'geocode_status' => Property::GEOCODE_RESOLVED,
                            'latitude' => -36.8485 + (($pIdx % 10) * 0.005),
                            'longitude' => 174.7633 + (($pIdx % 10) * 0.005),
                            'created_by' => $supervisor?->id,
                        ]
                    );

                    // Sync beds
                    if (! empty($pData['bed_count'])) {
                        foreach ($pData['bed_count'] as $bKey => $qty) {
                            if ($qty > 0 && isset($bedMap[$bKey])) {
                                PropertyBed::updateOrCreate(
                                    ['property_id' => $property->id, 'bed_type_id' => $bedMap[$bKey]],
                                    ['quantity' => $qty, 'room_name' => 'Main']
                                );
                            }
                        }
                    }

                    // Sync linens
                    if (! empty($pData['linen_count'])) {
                        foreach ($pData['linen_count'] as $lKey => $qty) {
                            if ($qty > 0 && isset($linenMap[$lKey])) {
                                PropertyLinen::updateOrCreate(
                                    ['property_id' => $property->id, 'linen_type_id' => $linenMap[$lKey]],
                                    ['quantity' => $qty]
                                );
                            }
                        }
                    }

                    // Assign supervisor & cleaners if available
                    if ($supervisor) {
                        \App\Models\PropertyAssignment::firstOrCreate([
                            'property_id' => $property->id,
                            'assignable_type' => 'user',
                            'assignable_id' => $supervisor->id,
                            'assignment_role' => \App\Models\PropertyAssignment::ROLE_SUPERVISOR,
                        ], [
                            'is_primary' => true,
                            'assigned_by' => $supervisor->id,
                            'start_date' => now()->toDateString(),
                        ]);
                    }

                    $assignedCleaner = ($pIdx % 2 === 0) ? $cleaner1 : $cleaner2;
                    if ($assignedCleaner) {
                        \App\Models\PropertyAssignment::firstOrCreate([
                            'property_id' => $property->id,
                            'assignable_type' => 'user',
                            'assignable_id' => $assignedCleaner->id,
                            'assignment_role' => \App\Models\PropertyAssignment::ROLE_CLEANER,
                        ], [
                            'is_primary' => true,
                            'assigned_by' => $supervisor?->id,
                            'start_date' => now()->toDateString(),
                        ]);
                    }
                }
            }
        }
    }
}
