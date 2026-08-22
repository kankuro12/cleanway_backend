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
            ['name' => 'King Bed', 'description' => '180 x 200 cm standard king mattress', 'sort_order' => 1],
            ['name' => 'Queen Bed', 'description' => '150 x 200 cm standard queen mattress', 'sort_order' => 2],
            ['name' => 'Double Bed', 'description' => '135 x 190 cm double mattress', 'sort_order' => 3],
            ['name' => 'Single Bed', 'description' => '90 x 190 cm single mattress', 'sort_order' => 4],
            ['name' => 'Bunk Bed', 'description' => 'Two stacked single beds (2x single sets)', 'sort_order' => 5],
            ['name' => 'Sofa Bed', 'description' => 'Pull-out sleeper couch', 'sort_order' => 6],
            ['name' => 'Crib / Cot', 'description' => 'Infant travel crib or wooden cot', 'sort_order' => 7],
        ];

        foreach ($bedTypes as $bt) {
            BedType::firstOrCreate(['name' => $bt['name']], $bt);
        }

        // 2. Seed Linen Types (id, name, rate)
        $linenTypes = [
            ['name' => 'King Sheet Set', 'rate' => 18.50, 'description' => '1x King fitted, 1x flat sheet, 2x pillowcases', 'sort_order' => 1],
            ['name' => 'Queen Sheet Set', 'rate' => 16.00, 'description' => '1x Queen fitted, 1x flat sheet, 2x pillowcases', 'sort_order' => 2],
            ['name' => 'Double Sheet Set', 'rate' => 14.50, 'description' => '1x Double fitted, 1x flat sheet, 2x pillowcases', 'sort_order' => 3],
            ['name' => 'Single Sheet Set', 'rate' => 12.00, 'description' => '1x Single fitted, 1x flat sheet, 1x pillowcase', 'sort_order' => 4],
            ['name' => 'Bath Towel', 'rate' => 4.50, 'description' => 'Standard white 600gsm bath towel', 'sort_order' => 5],
            ['name' => 'Hand Towel', 'rate' => 2.50, 'description' => 'White cotton hand towel', 'sort_order' => 6],
            ['name' => 'Bath Mat', 'rate' => 3.50, 'description' => 'Heavy cotton bath mat', 'sort_order' => 7],
            ['name' => 'Face Cloth / Washer', 'rate' => 1.50, 'description' => 'Small face washer cloth', 'sort_order' => 8],
            ['name' => 'Kitchen Tea Towel', 'rate' => 2.00, 'description' => 'Cotton kitchen tea towel', 'sort_order' => 9],
            ['name' => 'Duvet / Quilt Cover (King)', 'rate' => 9.00, 'description' => 'King duvet cover', 'sort_order' => 10],
            ['name' => 'Duvet / Quilt Cover (Queen)', 'rate' => 8.00, 'description' => 'Queen duvet cover', 'sort_order' => 11],
        ];

        foreach ($linenTypes as $lt) {
            LinenType::firstOrCreate(['name' => $lt['name']], $lt);
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
