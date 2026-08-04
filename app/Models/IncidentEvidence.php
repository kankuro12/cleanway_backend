<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'incident_id', 'uploader_id', 'file_path', 'original_filename',
    'mime_type', 'size_bytes', 'checksum', 'processing_status',
])]
class IncidentEvidence extends Model
{
    use Auditable;

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    protected function casts(): array
    {
        return ['size_bytes' => 'integer'];
    }
}
