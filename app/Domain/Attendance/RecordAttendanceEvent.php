<?php

namespace App\Domain\Attendance;

use App\Models\AttendanceEvent;
use App\Models\GpsException;
use App\Models\Property;
use App\Models\Shift;
use App\Models\Task;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Geocoding\EffectiveRadiusResolver;
use App\Support\Geodesic;

/**
 * Records attendance events (immutable) with GPS/geofence validation
 * (spec §11.2, §12.1) and integrity flags (spec §12.4).
 *
 * @param  array<string, mixed>  $payload
 */
class RecordAttendanceEvent
{
    public function __construct(
        private readonly EffectiveRadiusResolver $radiusResolver,
        private readonly AuditLogger $audit,
    ) {}

    public function execute(User $user, string $eventType, array $payload = []): AttendanceEvent
    {
        $target = $payload['task'] ?? ($payload['property'] ?? null);
        $distance = null;
        $inside = null;
        $radius = null;
        $propertyId = $target instanceof Property ? $target->id : ($payload['property_id'] ?? null);
        $flags = [];

        if ($target instanceof Task && $target->latitude_snapshot !== null && $target->longitude_snapshot !== null) {
            // Task-level snapshot wins for task GPS events.
            $radius = $target->check_in_radius_snapshot
                ?? $this->radiusResolver->resolve($target->property ?? new Property);
            $propertyId = $target->property_id;

            if (isset($payload['latitude'], $payload['longitude'])) {
                $distance = round(Geodesic::distanceMeters(
                    (float) $payload['latitude'],
                    (float) $payload['longitude'],
                    (float) $target->latitude_snapshot,
                    (float) $target->longitude_snapshot,
                ), 2);
                $inside = $distance <= $radius;
            }
        } elseif ($target instanceof Property && $target->latitude !== null && $target->longitude !== null) {
            $radius = $this->radiusResolver->resolve($target);

            if (isset($payload['latitude'], $payload['longitude'])) {
                $distance = round(Geodesic::distanceMeters(
                    (float) $payload['latitude'],
                    (float) $payload['longitude'],
                    (float) $target->latitude,
                    (float) $target->longitude,
                ), 2);
                $inside = $distance <= $radius;
            }
        } elseif ($target instanceof Property && $target->latitude === null) {
            $flags['missing_coordinates'] = true;
        } elseif ($target instanceof Task && $target->latitude_snapshot === null) {
            $flags['missing_coordinates'] = true;
        }

        if (isset($payload['latitude'], $payload['longitude'])) {
            $accuracy = $payload['gps_accuracy_meters'] ?? null;

            if ($accuracy !== null && $accuracy > (int) config('gps.max_accuracy_meters')) {
                $flags['low_accuracy'] = true;
            }

            if (! empty($payload['is_mock_location'])) {
                $flags['mock_location'] = true;
            }

            if (! empty($payload['device_timestamp']) && $payload['device_timestamp'] !== null) {
                $server = now();
                $device = \Carbon\Carbon::parse($payload['device_timestamp']);
                $diffMinutes = abs($server->diffInMinutes($device));

                if ($diffMinutes > 10) {
                    $flags['device_time_difference'] = $diffMinutes;
                }
            }

            if (! empty($payload['offline'])) {
                $flags['offline_submission'] = true;
            }
        }

        $insideGeofence = $inside === null ? null : (bool) $inside;

        $event = AttendanceEvent::create([
            'user_id' => $user->id,
            'shift_id' => $payload['shift_id'] ?? $this->activeShiftId($user),
            'task_id' => $payload['task_id'] ?? null,
            'event_type' => $eventType,
            'server_timestamp' => now(),
            'device_timestamp' => $payload['device_timestamp'] ?? null,
            'latitude' => $payload['latitude'] ?? null,
            'longitude' => $payload['longitude'] ?? null,
            'gps_accuracy_meters' => $payload['gps_accuracy_meters'] ?? null,
            'effective_radius_meters' => $radius,
            'property_id' => $propertyId,
            'distance_from_property_meters' => $distance,
            'inside_geofence' => $insideGeofence,
            'device_id' => $payload['device_id'] ?? null,
            'source' => $payload['source'] ?? 'api',
            'offline' => (bool) ($payload['offline'] ?? false),
            'synced_at' => ($payload['offline'] ?? false) ? now() : null,
            'remarks' => $payload['remarks'] ?? null,
            'integrity_flags' => $flags ?: null,
        ]);

        $this->applyGeofencePolicy($event, $payload, $flags);

        $this->audit->log("attendance.{$eventType}", 'attendance_event', $event->id, [
            'after' => $event->only(['event_type', 'inside_geofence', 'distance_from_property_meters', 'effective_radius_meters']),
            'actor_id' => $user->id,
        ]);

        return $event;
    }

    private function activeShiftId(User $user): ?int
    {
        return Shift::where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->whereIn('status', [Shift::STATUS_CONFIRMED, Shift::STATUS_IN_PROGRESS])
            ->value('id');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $flags
     */
    private function applyGeofencePolicy(AttendanceEvent $event, array $payload, array $flags): void
    {
        if (empty($flags['missing_coordinates'])) {
            if ($event->inside_geofence === null || $event->inside_geofence === true) {
                return;
            }

            $policy = (string) config('gps.out_of_radius_policy');

            if ($policy === GpsException::POLICY_REJECT) {
                // Event stays as a record of the attempt; check-in caller blocks.
            } elseif ($policy === GpsException::POLICY_ACCEPT) {
                return;
            } else {
                GpsException::create([
                    'event_id' => $event->id,
                    'task_id' => $payload['task_id'] ?? null,
                    'policy' => $policy,
                    'reason' => 'Outside permitted check-in radius ('.$event->distance_from_property_meters.' m > '.$event->effective_radius_meters.' m).',
                    'integrity_flags' => $flags ?: null,
                ]);
            }

            return;
        }

        // Property without coordinates (spec §12.2): record exception unless policy accepts.
        $policy = (string) config('gps.missing_coordinates_policy');

        if ($policy !== GpsException::POLICY_ACCEPT) {
            GpsException::create([
                'event_id' => $event->id,
                'task_id' => $payload['task_id'] ?? null,
                'policy' => $policy,
                'reason' => 'Property has no resolved coordinates — GPS verification unavailable.',
                'integrity_flags' => $flags ?: null,
            ]);
        }
    }
}
