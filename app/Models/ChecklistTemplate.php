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
    'name', 'slug', 'description', 'active',
])]
class ChecklistTemplate extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public function sections(): HasMany
    {
        return $this->hasMany(ChecklistSection::class)->orderBy('sort_order');
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'checklist';
        $slug = $base;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
