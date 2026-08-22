<?php

namespace App\Domain\Properties;

use App\Jobs\GeocodeProperty;
use App\Models\Property;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class CreateProperty
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function execute(array $data, ?User $actor = null): Property
    {
        if (empty($data['property_code'])) {
            $data['property_code'] = 'P-'.strtoupper(\Illuminate\Support\Str::random(4));
        }

        $property = Property::create($data + ['created_by' => $actor?->id]);

        $needsGeocode = $property->latitude === null || $property->longitude === null
            || $property->geocode_status === Property::GEOCODE_FAILED;

        if ($needsGeocode) {
            GeocodeProperty::dispatch($property->id);
        }

        $this->audit->log('property.created', 'property', $property->id, [
            'after' => [
                'name' => $property->name,
                'address' => $property->address,
                'geocode_status' => $property->geocode_status,
            ],
            'actor_id' => $actor?->id,
        ]);

        return $property;
    }
}
