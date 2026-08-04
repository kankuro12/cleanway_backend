<?php

namespace App\Jobs;

use App\Models\TaskEvidence;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Evidence post-processing: checksum + image dimensions, then ready.
 * Real compression/thumbnails drop in here later (spec §13.1).
 */
class ProcessEvidenceImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(public readonly int $evidenceId) {}

    public function handle(): void
    {
        $evidence = TaskEvidence::find($this->evidenceId);

        if (! $evidence) {
            return;
        }

        $path = Storage::disk('evidence')->path($evidence->file_path);

        if (! file_exists($path)) {
            $evidence->update(['processing_status' => TaskEvidence::STATUS_FAILED]);

            return;
        }

        $evidence->update([
            'checksum' => hash_file('sha256', $path),
            'size_bytes' => filesize($path),
            'mime_type' => mime_content_type($path) ?: $evidence->mime_type,
        ]);

        $size = @getimagesize($path);
        $evidence->update([
            'width' => $size[0] ?? null,
            'height' => $size[1] ?? null,
            'processing_status' => TaskEvidence::STATUS_READY,
        ]);
    }
}
