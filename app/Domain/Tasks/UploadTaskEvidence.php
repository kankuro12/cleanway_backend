<?php

namespace App\Domain\Tasks;

use App\Jobs\ProcessEvidenceImage;
use App\Models\Task;
use App\Models\TaskEvidence;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;

class UploadTaskEvidence
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $meta
     */
    public function execute(Task $task, User $uploader, UploadedFile $file, string $evidenceType, array $meta = []): TaskEvidence
    {
        $path = $file->store("tasks/{$task->id}", 'evidence');

        $evidence = TaskEvidence::create([
            'task_id' => $task->id,
            'uploader_id' => $uploader->id,
            'evidence_type' => $evidenceType,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'captured_at' => $meta['captured_at'] ?? now(),
            'uploaded_at' => now(),
            'latitude' => $meta['latitude'] ?? null,
            'longitude' => $meta['longitude'] ?? null,
            'device_id' => $meta['device_id'] ?? null,
            'source' => $meta['source'] ?? 'api',
            'processing_status' => TaskEvidence::STATUS_PENDING,
        ]);

        ProcessEvidenceImage::dispatch($evidence->id);

        $this->audit->log('task.evidence_uploaded', 'task_evidence', $evidence->id, [
            'after' => ['task_id' => $task->id, 'evidence_type' => $evidenceType, 'size_bytes' => $evidence->size_bytes],
            'actor_id' => $uploader->id,
        ]);

        return $evidence;
    }
}
