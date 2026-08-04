<?php

namespace App\Domain\Tasks;

use App\Mail\TaskAssignedMail;
use App\Models\NotificationDelivery;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

class AssignTask
{
    public function __construct(
        private readonly TaskSchedulingValidator $validator,
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @return array{warnings: array<int, string>}
     */
    public function execute(Task $task, string $assigneeType, int $assigneeId, ?User $actor = null, bool $overrideWarnings = false, ?string $overrideReason = null): array
    {
        $assignee = $assigneeType === 'team' ? Team::find($assigneeId) : User::find($assigneeId);

        if (! $assignee) {
            throw new \InvalidArgumentException('Assignable not found.');
        }

        $result = $this->validator->validate($task, $assignee instanceof User ? $assignee : null);

        if ($result['errors']) {
            throw new \InvalidArgumentException(implode(' ', $result['errors']));
        }

        if ($result['warnings'] && ! $overrideWarnings) {
            return ['warnings' => $result['warnings']];
        }

        return DB::transaction(function () use ($task, $assigneeType, $assigneeId, $actor, $overrideReason, $assignee): array {
            $assignment = TaskAssignment::updateOrCreate(
                ['task_id' => $task->id, 'assignee_type' => $assigneeType, 'assignee_id' => $assigneeId],
                [
                    'assigned_at' => now(),
                    'assigned_by' => $actor?->id,
                    'status' => TaskAssignment::STATUS_PENDING,
                ]
            );

            if ($task->status === Task::STATUS_SCHEDULED || $task->status === Task::STATUS_UNASSIGNED || $task->status === Task::STATUS_DRAFT) {
                $task->update(['status' => Task::STATUS_ASSIGNED]);
            }

            $this->audit->log('task.assigned', 'task', $task->id, [
                'after' => [
                    'assignment_id' => $assignment->id,
                    'assignee_type' => $assigneeType,
                    'assignee_id' => $assigneeId,
                    'override_reason' => $overrideReason,
                ],
                'actor_id' => $actor?->id,
            ]);

            if ($assignee instanceof User) {
                $this->notifications->send(
                    $assignee,
                    'task.assigned',
                    'New task assigned',
                    "{$task->title} — {$task->scheduled_start_at?->format('D j M H:i')}",
                    ['task_id' => $task->id, 'assignment_id' => $assignment->id],
                    "task.assigned:{$task->id}:{$assignment->id}",
                    [
                        NotificationDelivery::CHANNEL_IN_APP,
                        NotificationDelivery::CHANNEL_EMAIL,
                        NotificationDelivery::CHANNEL_PUSH,
                    ],
                    new TaskAssignedMail($task),
                );
            }

            return ['warnings' => []];
        });
    }

    public function remove(Task $task, TaskAssignment $assignment, ?User $actor = null): void
    {
        DB::transaction(function () use ($task, $assignment, $actor): void {
            $assignment->delete();

            if (! $task->assignments()->exists()) {
                $task->update(['status' => Task::STATUS_UNASSIGNED]);
            }

            $this->audit->log('task.unassigned', 'task', $task->id, [
                'after' => ['assignment_id' => $assignment->id],
                'actor_id' => $actor?->id,
            ]);
        });
    }
}
