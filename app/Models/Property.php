<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'uuid', 'name', 'address', 'formatted_address', 'google_place_id',
    'latitude', 'longitude', 'geocode_accuracy', 'geocode_status', 'geocode_hash',
    'geocoded_at', 'location_source', 'permitted_check_in_radius_meters',
    'property_category_id', 'contact_name', 'contact_phone', 'contact_email',
    'postal_code', 'access_instructions', 'parking_instructions', 'safety_instructions',
    'special_cleaning_requirements', 'service_frequency', 'active', 'internal_notes',
    'created_by', 'updated_by',
])]
class Property extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const GEOCODE_PENDING = 'pending';

    public const GEOCODE_RESOLVED = 'resolved';

    public const GEOCODE_MANUALLY_ADJUSTED = 'manually_adjusted';

    public const GEOCODE_FAILED = 'failed';

    public const GEOCODE_NOT_REQUESTED = 'not_requested';

    public const GEOCODE_STATUSES = [
        self::GEOCODE_PENDING,
        self::GEOCODE_RESOLVED,
        self::GEOCODE_MANUALLY_ADJUSTED,
        self::GEOCODE_FAILED,
        self::GEOCODE_NOT_REQUESTED,
    ];

    public const SOURCE_GOOGLE_PLACES = 'google_places';

    public const SOURCE_GOOGLE_GEOCODING = 'google_geocoding';

    public const SOURCE_MANUAL_PIN = 'manual_pin';

    public const SOURCE_IMPORTED = 'imported';

    public const SOURCE_UNKNOWN = 'unknown';

    public const SOURCES = [
        self::SOURCE_GOOGLE_PLACES,
        self::SOURCE_GOOGLE_GEOCODING,
        self::SOURCE_MANUAL_PIN,
        self::SOURCE_IMPORTED,
        self::SOURCE_UNKNOWN,
    ];

    protected static function booted(): void
    {
        static::creating(function (Property $property): void {
            $property->uuid ??= (string) Str::uuid();
            $property->geocode_hash ??= self::hashOf($property->name, $property->address);
        });

        static::updating(function (Property $property): void {
            if ($property->isDirty('name') || $property->isDirty('address')) {
                $property->geocode_hash = self::hashOf($property->name, $property->address);
            }
        });
    }

    public static function hashOf(string $name, string $address): string
    {
        return hash('sha256', mb_strtolower(trim($name)).'|'.mb_strtolower(trim($address)));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class, 'property_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PropertyTag::class, 'property_tag')->withTimestamps();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PropertyAssignment::class);
    }

    public function geocodeAttempts(): HasMany
    {
        return $this->hasMany(PropertyGeocodeAttempt::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSearch($query, ?string $term): void
    {
        $query->when($term, fn ($q, $term) => $q->where(fn ($q) => $q
            ->where('name', 'like', "%{$term}%")
            ->orWhere('address', 'like', "%{$term}%")
            ->orWhere('formatted_address', 'like', "%{$term}%")
            ->orWhere('google_place_id', 'like', "%{$term}%")
            ->orWhere('contact_name', 'like', "%{$term}%")
            ->orWhere('contact_phone', 'like', "%{$term}%")));
    }

    public function scopeFilter($query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->search($s))
            ->when(isset($filters['active']), fn ($q, $v) => $q->where('active', (bool) $v))
            ->when($filters['category_id'] ?? null, fn ($q, $v) => $q->where('property_category_id', $v))
            ->when($filters['geocode_status'] ?? null, fn ($q, $v) => $q->where('geocode_status', $v))
            ->when(! empty($filters['missing_coords']), fn ($q) => $q->whereNull('latitude')->orWhereNull('longitude'))
            ->when(! empty($filters['unassigned']), fn ($q) => $q->whereDoesntHave('assignments'))
            ->when($filters['tag_id'] ?? null, fn ($q, $v) => $q->whereHas('tags', fn ($q) => $q->whereKey($v)))
            ->when($filters['assigned_to'] ?? null, fn ($q, $v) => $q->whereHas('assignments', fn ($q) => $q->where('assignable_id', $v)));
    }

    public function scopeWithCoords($query): void
    {
        $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'permitted_check_in_radius_meters' => 'integer',
            'active' => 'boolean',
            'geocoded_at' => 'datetime',
        ];
    }
}
