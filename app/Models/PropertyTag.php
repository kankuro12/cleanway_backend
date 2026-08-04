<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'description', 'active', 'color', 'sort_order',
])]
class PropertyTag extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_tag')->withTimestamps();
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'tag';
        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
