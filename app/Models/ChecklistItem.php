<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'checklist_section_id', 'label', 'item_type', 'required', 'is_photo_required', 'is_comment_required', 'issue_triggering', 'sort_order',
])]
class ChecklistItem extends Model
{
    public const TYPES = ['yes_no', 'pass_fail', 'text', 'numeric', 'photo'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ChecklistSection::class);
    }

    protected function casts(): array
    {
        return [
            'required' => 'boolean',
            'is_photo_required' => 'boolean',
            'is_comment_required' => 'boolean',
            'issue_triggering' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
