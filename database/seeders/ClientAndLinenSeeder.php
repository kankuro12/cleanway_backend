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
        // 1. Seed Bed Types
        $bedTypes = [
            ['name' => 'Super King Bed', 'description' => '200 x 200 cm super king mattress', 'sort_order' => 1],
            ['name' => 'Queen Bed', 'description' => '150 x 200 cm standard queen mattress', 'sort_order' => 2],
            ['name' => 'King Bed', 'description' => '180 x 200 cm standard king mattress', 'sort_order' => 3],
            ['name' => 'Double Bed', 'description' => '135 x 190 cm double mattress', 'sort_order' => 4],
            ['name' => 'Single Bed', 'description' => '90 x 190 cm single mattress', 'sort_order' => 5],
            ['name' => 'Bunk Bed', 'description' => 'Two stacked single beds (2x single sets)', 'sort_order' => 6],
            ['name' => 'Sofa Bed', 'description' => 'Pull-out sleeper couch', 'sort_order' => 7],
            ['name' => 'Crib / Cot', 'description' => 'Infant travel crib or wooden cot', 'sort_order' => 8],
        ];

        foreach ($bedTypes as $bt) {
            BedType::updateOrCreate(['name' => $bt['name']], $bt);
        }

        // 2. Seed Linen Types (id, name, rate)
        $linenTypes = [
            ['name' => 'Single King', 'rate' => 18.00, 'description' => 'Single King Sheet', 'sort_order' => 1],
            ['name' => 'Super King Sheet', 'rate' => 19.00, 'description' => 'Super King Sheet', 'sort_order' => 2],
            ['name' => 'Queen Sheet', 'rate' => 16.00, 'description' => 'Queen Sheet', 'sort_order' => 3],
            ['name' => 'Single Sheet', 'rate' => 12.00, 'description' => 'Single Sheet', 'sort_order' => 4],
            ['name' => 'Pillow Slip', 'rate' => 3.00, 'description' => 'Pillow Slip / Case', 'sort_order' => 5],
            ['name' => 'Bath Towels', 'rate' => 4.50, 'description' => 'Standard Bath Towels', 'sort_order' => 6],
            ['name' => 'Face cloths', 'rate' => 1.50, 'description' => 'Face cloths / Washers', 'sort_order' => 7],
            ['name' => 'Hand Towels', 'rate' => 2.50, 'description' => 'Cotton Hand Towels', 'sort_order' => 8],
            ['name' => 'Bath Matt', 'rate' => 3.50, 'description' => 'Bath Matt', 'sort_order' => 9],
            ['name' => 'Tea Towels', 'rate' => 2.00, 'description' => 'Kitchen Tea Towels', 'sort_order' => 10],
        ];

        foreach ($linenTypes as $lt) {
            LinenType::updateOrCreate(['name' => $lt['name']], $lt);
        }

        // 3. Seed Sample Clients
        $clients = [
            [
                'name' => 'James Harrison',
                'company_name' => 'Harrison Holiday Homes Ltd',
                'email' => 'james@harrisonrentals.co.nz',
                'phone' => '+64 21 555 0192',
                'address' => '45 Queen Street, Auckland CBD',
                'billing_address' => 'PO Box 1029, Auckland 1140',
                'notes' => 'VIP property owner with multiple Airbnb apartments.',
                'active' => true,
            ],
            [
                'name' => 'Elena Rostova',
                'company_name' => 'Skyline Stay Auckland',
                'email' => 'elena@skylinestay.co.nz',
                'phone' => '+64 22 839 1044',
                'address' => '12 Viaduct Harbour Ave, Auckland',
                'billing_address' => '12 Viaduct Harbour Ave, Auckland',
                'notes' => 'Requires photo proof on all linen turnovers.',
                'active' => true,
            ],
            [
                'name' => 'David & Linda Chen',
                'company_name' => 'Chen Coastal Retreats',
                'email' => 'chen.rentals@xtra.co.nz',
                'phone' => '+64 27 492 8110',
                'address' => '88 Tamaki Drive, Mission Bay',
                'notes' => 'Keys located in lockbox #402.',
                'active' => true,
            ],
        ];

        foreach ($clients as $c) {
            Client::firstOrCreate(['email' => $c['email']], $c);
        }

        // 4. Attach Clients, Beds, and Linens to existing properties if any
        $properties = Property::all();
        $sampleClients = Client::all();
        $sampleBeds = BedType::all();
        $sampleLinens = LinenType::all();

        if ($properties->isNotEmpty() && $sampleClients->isNotEmpty()) {
            foreach ($properties as $idx => $property) {
                // Assign a client
                $client = $sampleClients[$idx % $sampleClients->count()];
                $property->update([
                    'client_id' => $client->id,
                    'bedrooms_count' => $property->bedrooms_count ?: 2,
                    'bathrooms_count' => $property->bathrooms_count ?: 1.5,
                    'parking_type' => $property->parking_type ?: 'garage',
                    'parking_spaces_count' => $property->parking_spaces_count ?: 1,
                ]);

                // Assign sample beds if empty
                if ($property->beds()->count() === 0 && $sampleBeds->isNotEmpty()) {
                    PropertyBed::create([
                        'property_id' => $property->id,
                        'bed_type_id' => $sampleBeds->firstWhere('name', 'King Bed')?->id ?? $sampleBeds->first()->id,
                        'quantity' => 1,
                        'room_name' => 'Master Bedroom',
                    ]);
                    if ($sampleBeds->count() > 1) {
                        PropertyBed::create([
                            'property_id' => $property->id,
                            'bed_type_id' => $sampleBeds->firstWhere('name', 'Single Bed')?->id ?? $sampleBeds->skip(1)->first()->id,
                            'quantity' => 2,
                            'room_name' => 'Guest Bedroom',
                        ]);
                    }
                }

                // Assign sample linens if empty
                if ($property->linens()->count() === 0 && $sampleLinens->isNotEmpty()) {
                    PropertyLinen::create([
                        'property_id' => $property->id,
                        'linen_type_id' => $sampleLinens->firstWhere('name', 'King Sheet Set')?->id ?? $sampleLinens->first()->id,
                        'quantity' => 1,
                        'custom_rate' => null,
                        'notes' => '1 set for master bed',
                    ]);
                    PropertyLinen::create([
                        'property_id' => $property->id,
                        'linen_type_id' => $sampleLinens->firstWhere('name', 'Bath Towel')?->id ?? $sampleLinens->skip(1)->first()->id,
                        'quantity' => 4,
                        'custom_rate' => null,
                        'notes' => '2 per guest',
                    ]);
                }
            }
        }
    }
}
