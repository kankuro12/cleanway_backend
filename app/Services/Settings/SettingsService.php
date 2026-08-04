<?php

namespace App\Services\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Cached key/value settings (spec plan: radius default, accuracy threshold,
 * policies). Cache `settings:{scope}` invalidated on every write.
 */
class SettingsService
{
    private const CACHE_KEY = 'settings:';

    public function get(string $key, mixed $default = null, string $scope = Setting::SCOPE_ORGANIZATION): mixed
    {
        $all = $this->all($scope);

        return $all[$key] ?? $default;
    }

    public function set(string $key, mixed $value, string $scope = Setting::SCOPE_ORGANIZATION, ?string $description = null): void
    {
        Setting::updateOrCreate(
            ['scope' => $scope, 'key' => $key],
            ['value' => is_scalar($value) ? (string) $value : json_encode($value), 'description' => $description]
        );

        $this->forget($scope);
    }

    public function all(string $scope = Setting::SCOPE_ORGANIZATION): array
    {
        return Cache::rememberForever(self::CACHE_KEY.$scope, function () use ($scope): array {
            return Setting::where('scope', $scope)->pluck('value', 'key')->all();
        });
    }

    public function forget(string $scope): void
    {
        Cache::forget(self::CACHE_KEY.$scope);
    }

    /**
     * Override runtime config from persisted settings (called at boot).
     */
    public function applyToConfig(): void
    {
        $map = [
            Setting::SCOPE_SYSTEM => [
                'gps_max_accuracy_meters' => 'gps.max_accuracy_meters',
                'gps_require_checkout' => 'gps.require_gps_checkout',
                'task_require_completion_remarks' => 'gps.require_completion_remarks',
                'task_require_incident_ack' => 'gps.require_incident_acknowledgement',
            ],
            Setting::SCOPE_ORGANIZATION => [
                'default_check_in_radius_meters' => 'organization.default_check_in_radius_meters',
                'gps_out_of_radius_policy' => 'gps.out_of_radius_policy',
                'gps_missing_coordinates_policy' => 'gps.missing_coordinates_policy',
            ],
        ];

        foreach ($map as $scope => $keys) {
            foreach ($this->all($scope) as $key => $value) {
                if (isset($keys[$key])) {
                    config([$keys[$key] => is_numeric($value) ? (int) $value : $value]);
                }
            }
        }
    }
}
