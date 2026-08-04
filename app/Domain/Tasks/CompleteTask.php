<?php

namespace App\Domain\Tasks;

use App\Models\AttendanceEvent;
use App\Models\Task;
use App\Models\TaskChecklistResponse;
use App\Models\User;

/**
 * Completion gate (spec §13.2): required checklist items answered, minimum
 * before/after photos, required remarks, GPS check-out, incident ack.
 */
class CompleteTask
{
    public function __construct(private readonly TransitionTaskStatus $transitioner) {}

    /**
     * @param  array<int, array{snapshot_item_id: int, value: string}>  $responses
     * @param  array<string, mixed>  $context
     *
     * @return array{ok: bool, missing: array<int, string>}
     */
    public function execute(Task $task, User $actor, array $responses = [], string $remarks = '', array $context = []): array
    {
        $missing = [];
        $snapshot = $task->checklistSnapshot;

        foreach ($snapshot->where('required', true) as $item) {
            $answered = collect($responses)->contains('snapshot_item_id', $item->id);

            if (! $answered) {
                $missing[] = "Required checklist item unanswered: {$item->item_label}";
            }
        }

        $typeSnapshot = $task->task_type_snapshot ?? [];
        $beforePhotos = $task->evidence()->where('evidence_type', 'before')->where('processing_status', '!=', 'failed')->count();
        $afterPhotos = $task->evidence()->where('evidence_type', 'after')->where('processing_status', '!=', 'failed')->count();

        if (($typeSnapshot['before_photo_required'] ?? false) && $beforePhotos < 1) {
            $missing[] = 'Minimum 1 before photo required.';
        }

        if (($typeSnapshot['after_photo_required'] ?? true) && $afterPhotos < 1) {
            $missing[] = 'Minimum 1 after photo required.';
        }

        $minPhotos = (int) ($typeSnapshot['minimum_photo_count'] ?? 0);
        if ($beforePhotos + $afterPhotos < $minPhotos) {
            $missing[] = "Minimum {$minPhotos} photos required.";
        }

        if (config('gps.require_completion_remarks') && trim($remarks) === '' && $task->description === null) {
            $missing[] = 'Completion remarks required.';
        }

        if (config('gps.require_gps_checkout') && ! $task->events()->where('event_type', AttendanceEvent::TYPE_CLOCK_OUT)->exists()) {
            $missing[] = 'GPS check-out required before completion.';
        }

        if (config('gps.require_incident_acknowledgement') && $task->incidents()->whereIn('status', ['open', 'acknowledged', 'investigating'])->exists()) {
            $missing[] = 'Open incidents must be acknowledged or resolved.';
        }

        if ($missing) {
            return ['ok' => false, 'missing' => $missing];
        }

        foreach ($responses as $response) {
            TaskChecklistResponse::create([
                'task_id' => $task->id,
                'snapshot_item_id' => $response['snapshot_item_id'],
                'value' => $response['value'],
                'answered_by' => $actor->id,
                'answered_at' => now(),
            ]);
        }

        $this->transitioner->transition($task, Task::STATUS_COMPLETED, $actor, $context + ['remarks' => $remarks ?: null]);

        return ['ok' => true, 'missing' => []];
    }
}
