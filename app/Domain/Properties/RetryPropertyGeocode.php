<?php

namespace App\Domain\Properties;

use App\Models\Property;
use App\Services\Audit\AuditLogger;

class RetryPropertyGeocode
{
    public function __construct(
        private readonly ResolvePropertyCoordinates $resolver,
        private readonly AuditLogger $audit,
    ) {}

    public function execute(Property $property, ?float $latitude = null, ?float $longitude = null): Property
    {
        $result = $this->resolver->execute($property, $latitude !== null ? (string) $latitude : null, $longitude !== null ? (string) $longitude : null);

        $this->audit->log('property.geocode_retry', 'property', $property->id, [
            'after' => ['geocode_status' => $result['status']],
        ]);

        return $property->fresh();
    }
}
