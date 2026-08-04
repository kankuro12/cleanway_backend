<?php

namespace App\Domain\Incidents;

use App\Models\Incident;
use App\Models\IncidentEvidence;
use App\Models\Task;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class RaiseIncident
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $files
     */
    public function execute(User $reporter, array $data, array $files = []): Incident
    {
        return DB::transaction(function () use ($reporter, $data, $files): Incident {
            $incident = Incident::create([
                'task_id' => $data['task_id'] ?? null,
                'property_id' => $data['property_id'] ?? null,
                'reporter_id' => $reporter->id,
                'category' => $data['category'],
                'severity' => $data['severity'] ?? 'medium',
                'description' => $data['description'],
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'assigned_reviewer_id' => $data['assigned_reviewer_id'] ?? null,
            ]);

            foreach ($files as $file) {
                $path = $file->store("incidents/{$incident->id}", 'evidence');

                IncidentEvidence::create([
                    'incident_id' => $incident->id,
                    'uploader_id' => $reporter->id,
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'processing_status' => 'ready',
                ]);
            }

            $this->audit->log('incident.raised', 'incident', $incident->id, [
                'after' => ['category' => $incident->category, 'severity' => $incident->severity, 'task_id' => $incident->task_id],
                'actor_id' => $reporter->id,
            ]);

            return $incident;
        });
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function transition(Incident $incident, string $newStatus, ?User $actor = null, array $meta = []): Incident
    {
        if (! in_array($newStatus, Incident::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid incident status.');
        }

        $updates = ['status' => $newStatus];

        if (in_array($newStatus, [Incident::STATUS_RESOLVED, Incident::STATUS_CLOSED], true)) {
            $updates['resolved_at'] = now();
            $updates['resolution'] = $meta['resolution'] ?? $incident->resolution;
        }

        $incident->update($updates);

        $this->audit->log("incident.{$newStatus}", 'incident', $incident->id, [
            'after' => ['status' => $newStatus],
            'actor_id' => $actor?->id,
        ]);

        return $incident->fresh();
    }
}
