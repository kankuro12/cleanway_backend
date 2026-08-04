<?php

namespace App\Services\Geocoding;

use App\Models\Property;

/**
 * Effective check-in radius fallback chain (spec §2.3):
 * property → category → organization settings → system fallback.
 */
class EffectiveRadiusResolver
{
    public const SYSTEM_FALLBACK_METERS = 150;

    public function resolve(Property $property): int
    {
        if ($property->permitted_check_in_radius_meters !== null) {
            return (int) $property->permitted_check_in_radius_meters;
        }

        $categoryRadius = $property->category?->default_check_in_radius_meters;

        if ($categoryRadius !== null) {
            return (int) $categoryRadius;
        }

        $orgRadius = config('organization.default_check_in_radius_meters');

        if (is_numeric($orgRadius)) {
            return (int) $orgRadius;
        }

        return self::SYSTEM_FALLBACK_METERS;
    }
}
