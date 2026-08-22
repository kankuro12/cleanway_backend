<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'property_id', 'linen_type_id', 'quantity', 'custom_rate', 'notes',
])]
class PropertyLinen extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'custom_rate' => 'decimal:2',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function linenType(): BelongsTo
    {
        return $this->belongsTo(LinenType::class);
    }

    /**
     * Effective unit rate (custom rate if specified, otherwise the linen type rate).
     */
    public function getEffectiveRateAttribute(): float
    {
        if ($this->custom_rate !== null) {
            return (float) $this->custom_rate;
        }

        return (float) ($this->linenType?->rate ?? 0.0);
    }

    /**
     * Total cost for this linen configuration (quantity * effective rate).
     */
    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->effective_rate;
    }
}
