<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'scope', 'key', 'value', 'description',
])]
class Setting extends Model
{
    use Auditable;

    public const SCOPE_SYSTEM = 'system';

    public const SCOPE_ORGANIZATION = 'organization';

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
