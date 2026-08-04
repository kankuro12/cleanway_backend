<?php

namespace App\Domain\Properties;

use App\Models\Property;
use App\Models\PropertyGeocodeAttempt;
use App\Services\Audit\AuditLogger;
use App\Services\Geocoding\GooglePlaces;

class ResolvePropertyCoordinates
{
    public function __construct(
        private readonly GooglePlaces $places,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Resolve coordinates for a property. Uses Place Details when the property
     * carries a Google Place ID, otherwise the geocoding fallback.
     *
     * @return array{status: string, resolved: bool}
     */
    public function execute(Property $property, ?string $manualLatitude = null, ?string $manualLongitude = null): array
    {
        if ($manualLatitude !== null && $manualLongitude !== null) {
            return $this->applyManualPin($property, (float) $manualLatitude, (float) $manualLongitude);
        }

        // Never re-geocode an unchanged address that already resolved or failed.
        if (in_array($property->geocode_status, [
            Property::GEOCODE_RESOLVED,
            Property::GEOCODE_MANUALLY_ADJUSTED,
            Property::GEOCODE_FAILED,
        ], true) && $property->geocode_hash === Property::hashOf($property->name, $property->address)) {
            return ['status' => $property->geocode_status, 'resolved' => false];
        }

        $query = trim($property->formatted_address ?: $property->address);
        $result = $this->places->configured()
            ? ($property->google_place_id
                ? $this->toGeocodeResult($this->places->placeDetails($property->google_place_id), $query)
                : ($this->places->geocode($property->name.' '.$query) ?: $this->places->geocode($query)))
            : [];

        $attempt = PropertyGeocodeAttempt::create([
            'property_id' => $property->id,
            'query' => $query,
            'status' => $result ? 'resolved' : 'failed',
            'result_json' => $result,
            'score' => $result['score'] ?? null,
            'attempted_at' => now(),
        ]);

        if (! $result) {
            $property->update([
                'geocode_status' => Property::GEOCODE_FAILED,
                'geocoded_at' => now(),
            ]);

            $this->audit->log('property.geocode_failed', 'property', $property->id, [
                'after' => ['geocode_status' => Property::GEOCODE_FAILED, 'attempt_id' => $attempt->id],
            ]);

            return ['status' => Property::GEOCODE_FAILED, 'resolved' => false];
        }

        $property->update([
            'formatted_address' => $property->formatted_address ?: $result['formatted_address'],
            'latitude' => $result['latitude'],
            'longitude' => $result['longitude'],
            'geocode_accuracy' => $result['accuracy'],
            'geocode_status' => Property::GEOCODE_RESOLVED,
            'location_source' => $property->google_place_id
                ? Property::SOURCE_GOOGLE_PLACES
                : Property::SOURCE_GOOGLE_GEOCODING,
            'geocoded_at' => now(),
        ]);

        $this->audit->log('property.geocoded', 'property', $property->id, [
            'after' => [
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
                'geocode_status' => Property::GEOCODE_RESOLVED,
                'source' => $property->location_source,
            ],
        ]);

        return ['status' => Property::GEOCODE_RESOLVED, 'resolved' => true];
    }

    /**
     * @return array{formatted_address: string, latitude: float, longitude: float, accuracy: string, score: float}|null
     */
    private function toGeocodeResult(?array $details, string $query): ?array
    {
        if (! $details) {
            return null;
        }

        return [
            'formatted_address' => $details['formatted_address'],
            'latitude' => $details['latitude'],
            'longitude' => $details['longitude'],
            'accuracy' => $details['accuracy'],
            'score' => $details['accuracy'] === 'rooftop' ? 100.0 : 70.0,
        ];
    }

    /**
     * @return array{status: string, resolved: bool}
     */
    private function applyManualPin(Property $property, float $latitude, float $longitude): array
    {
        $property->update([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'geocode_accuracy' => 'manual',
            'geocode_status' => Property::GEOCODE_MANUALLY_ADJUSTED,
            'location_source' => Property::SOURCE_MANUAL_PIN,
            'geocoded_at' => now(),
        ]);

        PropertyGeocodeAttempt::create([
            'property_id' => $property->id,
            'query' => $property->formatted_address ?: $property->address,
            'status' => 'manually_adjusted',
            'result_json' => ['latitude' => $latitude, 'longitude' => $longitude],
            'score' => null,
            'attempted_at' => now(),
        ]);

        $this->audit->log('property.pin_adjusted', 'property', $property->id, [
            'after' => ['latitude' => $latitude, 'longitude' => $longitude],
        ]);

        return ['status' => Property::GEOCODE_MANUALLY_ADJUSTED, 'resolved' => true];
    }
}
