<?php

namespace App\Domain\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Carbon;

class RescheduleTask
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @return array{warnings: array<int, string>}
     */
    public function execute(Task $task, Carbon $start, ?Carbon $end = null, ?User $actor = null): array
    {
        $warnings = [];

        if ($end && $end->lte($start)) {
            throw new \InvalidArgumentException('Scheduled end must be after scheduled start.');
        }

        $overlap = Task::query()
            ->whereKeyNot($task->id)
            ->where('status', '!=', Task::STATUS_CANCELLED)
            ->where('scheduled_start_at', '<', ($end ?? $start->copy()->addMinutes($task->estimated_duration_minutes ?: 60)))
            ->where('scheduled_end_at', '>', $start)
            ->exists();

        if ($overlap) {
            $warnings[] = 'Another task overlaps this time window.';
        }

        $task->update([
            'scheduled_start_at' => $start,
            'scheduled_end_at' => $end,
            'updated_by' => $actor?->id,
        ]);

        $this->audit->log('task.rescheduled', 'task', $task->id, [
            'after' => [
                'scheduled_start_at' => $task->scheduled_start_at->toIso8601String(),
                'scheduled_end_at' => $task->scheduled_end_at?->toIso8601String(),
            ],
            'actor_id' => $actor?->id,
        ]);

        $this->notifications->notifyTaskAssignees($task, 'task.rescheduled', 'Schedule changed', "{$task->title} was rescheduled.", ['task_id' => $task->id]);

        return ['warnings' => $warnings];
    }
}
