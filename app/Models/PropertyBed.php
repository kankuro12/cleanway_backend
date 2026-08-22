<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'property_id', 'bed_type_id', 'quantity', 'room_name',
])]
class PropertyBed extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function bedType(): BelongsTo
    {
        return $this->belongsTo(BedType::class);
    }
}
