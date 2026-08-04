<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'description', 'default_check_in_radius_meters',
    'default_task_type_id', 'default_checklist_id', 'default_manager_id',
    'default_team_id', 'default_safety_instructions', 'active', 'sort_order',
])]
class PropertyCategory extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'property_category_id');
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
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
            'default_check_in_radius_meters' => 'integer',
            'active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
