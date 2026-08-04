<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_id', 'uploader_id', 'evidence_type', 'file_path', 'original_filename',
    'mime_type', 'size_bytes', 'width', 'height', 'captured_at', 'uploaded_at',
    'latitude', 'longitude', 'device_id', 'source', 'checksum', 'processing_status',
])]
class TaskEvidence extends Model
{
    use Auditable;

    public const TYPES = ['before', 'during', 'after', 'issue', 'safety', 'access_problem', 'other'];

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_READY = 'ready';

    public const STATUS_FAILED = 'failed';

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'captured_at' => 'datetime',
            'uploaded_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
