<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'property_id', 'query', 'status', 'result_json', 'score', 'attempted_at',
])]
class PropertyGeocodeAttempt extends Model
{
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    protected function casts(): array
    {
        return [
            'result_json' => 'array',
            'score' => 'float',
            'attempted_at' => 'datetime',
        ];
    }
}
