<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'shift_id', 'event_type', 'server_timestamp', 'device_timestamp',
    'latitude', 'longitude', 'gps_accuracy_meters', 'effective_radius_meters',
    'property_id', 'distance_from_property_meters', 'inside_geofence',
    'device_id', 'source', 'offline', 'synced_at', 'remarks', 'integrity_flags',
])]
class AttendanceEvent extends Model
{
    use Auditable;

    public const TYPE_CLOCK_IN = 'clock_in';

    public const TYPE_BREAK_START = 'break_start';

    public const TYPE_BREAK_END = 'break_end';

    public const TYPE_CLOCK_OUT = 'clock_out';

    public const TYPE_MANUAL_CORRECTION = 'manual_correction';

    public const TYPE_SUPERVISOR_OVERRIDE = 'supervisor_override';

    public const TYPES = [
        self::TYPE_CLOCK_IN,
        self::TYPE_BREAK_START,
        self::TYPE_BREAK_END,
        self::TYPE_CLOCK_OUT,
        self::TYPE_MANUAL_CORRECTION,
        self::TYPE_SUPERVISOR_OVERRIDE,
    ];

    /**
     * Attendance events are immutable audit records (spec §11.3) — original
     * verified events are never rewritten; corrections add new events.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('Attendance events are immutable.');
    }

    public function delete(): ?bool
    {
        throw new \LogicException('Attendance events are immutable.');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function gpsException(): HasMany
    {
        return $this->hasMany(GpsException::class, 'event_id');
    }

    protected function casts(): array
    {
        return [
            'server_timestamp' => 'datetime',
            'device_timestamp' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'gps_accuracy_meters' => 'integer',
            'effective_radius_meters' => 'integer',
            'distance_from_property_meters' => 'float',
            'inside_geofence' => 'boolean',
            'offline' => 'boolean',
            'synced_at' => 'datetime',
            'integrity_flags' => 'array',
        ];
    }
}
