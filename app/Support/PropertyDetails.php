<?php

namespace App\Support;

use App\Models\Property;
use App\Models\PropertyBed;
use App\Models\PropertyLinen;
use Illuminate\Support\Facades\DB;

/**
 * Persists property pricing, specs, client association, beds and linens.
 */
class PropertyDetails
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function save(Property $property, array $data): void
    {
        DB::transaction(function () use ($property, $data): void {
            $payload = [];

            if (array_key_exists('client_id', $data)) {
                $payload['client_id'] = !empty($data['client_id']) ? (int) $data['client_id'] : null;
            }

            if (array_key_exists('bedrooms_count', $data)) {
                $payload['bedrooms_count'] = (int) ($data['bedrooms_count'] ?? 0);
            }

            if (array_key_exists('bathrooms_count', $data)) {
                $payload['bathrooms_count'] = (float) ($data['bathrooms_count'] ?? 1.0);
            }

            if (array_key_exists('parking_type', $data)) {
                $payload['parking_type'] = (string) ($data['parking_type'] ?? 'none');
            }

            if (array_key_exists('parking_spaces_count', $data)) {
                $payload['parking_spaces_count'] = (int) ($data['parking_spaces_count'] ?? 0);
            }

            if (isset($data['cleaning_duration_hours']) || isset($data['cleaning_duration_minutes'])) {
                $payload['cleaning_duration_minutes'] = (int) ($data['cleaning_duration_hours'] ?? 0) * 60 + (int) ($data['cleaning_duration_minutes'] ?? 0);
            }

            if (array_key_exists('client_fixed_amount', $data)) {
                $payload['client_fixed_amount'] = $data['client_fixed_amount'] !== null && $data['client_fixed_amount'] !== '' ? (float) $data['client_fixed_amount'] : null;
            }

            if (array_key_exists('cleaner_pay_type', $data)) {
                $payload['cleaner_pay_type'] = $data['cleaner_pay_type'] ?? 'per_hour';
            }

            if (array_key_exists('cleaner_fixed_amount', $data)) {
                $payload['cleaner_fixed_amount'] = $data['cleaner_fixed_amount'] !== null && $data['cleaner_fixed_amount'] !== '' ? (float) $data['cleaner_fixed_amount'] : null;
            }

            if (array_key_exists('cleaner_rate_per_hour', $data)) {
                $payload['cleaner_rate_per_hour'] = $data['cleaner_rate_per_hour'] !== null && $data['cleaner_rate_per_hour'] !== '' ? (float) $data['cleaner_rate_per_hour'] : null;
            }

            if (array_key_exists('parking_fee', $data)) {
                $payload['parking_fee'] = $data['parking_fee'] !== null && $data['parking_fee'] !== '' ? (float) $data['parking_fee'] : 0.00;
            }

            if (array_key_exists('needs_parking', $data)) {
                $payload['needs_parking'] = (bool) $data['needs_parking'];
            }

            if (!empty($payload)) {
                $property->update($payload);
            }

            // Sync Beds (persist only when quantity > 0)
            if (array_key_exists('beds', $data)) {
                $property->beds()->delete();
                foreach ($data['beds'] ?? [] as $bed) {
                    $qty = (int) ($bed['quantity'] ?? 0);
                    if (!empty($bed['bed_type_id']) && $qty > 0) {
                        PropertyBed::create([
                            'property_id' => $property->id,
                            'bed_type_id' => (int) $bed['bed_type_id'],
                            'quantity' => $qty,
                            'room_name' => !empty($bed['room_name']) ? (string) $bed['room_name'] : null,
                        ]);
                    }
                }
            }

            // Sync Linens (persist only when quantity > 0)
            if (array_key_exists('linens', $data)) {
                $property->linens()->delete();
                foreach ($data['linens'] ?? [] as $linen) {
                    $qty = (int) ($linen['quantity'] ?? 0);
                    if (!empty($linen['linen_type_id']) && $qty > 0) {
                        PropertyLinen::create([
                            'property_id' => $property->id,
                            'linen_type_id' => (int) $linen['linen_type_id'],
                            'quantity' => $qty,
                            'custom_rate' => isset($linen['custom_rate']) && $linen['custom_rate'] !== '' ? (float) $linen['custom_rate'] : null,
                            'notes' => !empty($linen['notes']) ? (string) $linen['notes'] : null,
                        ]);
                    }
                }
            }
        });
    }
}
