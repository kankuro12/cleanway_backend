<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'address', 'latitude', 'longitude', 'geofence_radius_meters', 'active'])]
class Branch extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'geofence_radius_meters' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }
}
