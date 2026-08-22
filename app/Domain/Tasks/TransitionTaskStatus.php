<?php

namespace App\Domain\Tasks;

use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskStatusHistory;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

/**
 * Explicit task state machine (spec §9.2). Every transition:
 *  - validates the caller + current state;
 *  - updates status timestamps;
 *  - writes a task_status_histories row;
 *  - writes an audit entry;
 *  - queues the matching notification.
 */
class TransitionTaskStatus
{
    /**
     * status => allowed next statuses.
     */
    public const TRANSITIONS = [
        Task::STATUS_DRAFT => [Task::STATUS_SCHEDULED, Task::STATUS_CANCELLED],
        Task::STATUS_SCHEDULED => [Task::STATUS_ASSIGNED, Task::STATUS_UNASSIGNED, Task::STATUS_CANCELLED],
        Task::STATUS_UNASSIGNED => [Task::STATUS_ASSIGNED, Task::STATUS_CANCELLED],
        Task::STATUS_ASSIGNED => [Task::STATUS_ACCEPTED, Task::STATUS_DECLINED, Task::STATUS_IN_PROGRESS, Task::STATUS_SUBMITTED_FOR_APPROVAL, Task::STATUS_CANCELLED],
        Task::STATUS_ACCEPTED => [Task::STATUS_IN_PROGRESS, Task::STATUS_DECLINED, Task::STATUS_CANCELLED],
        Task::STATUS_DECLINED => [Task::STATUS_CANCELLED],
        Task::STATUS_IN_PROGRESS => [Task::STATUS_PAUSED, Task::STATUS_DELAYED, Task::STATUS_UNABLE_TO_ACCESS, Task::STATUS_COMPLETED, Task::STATUS_CANCELLED],
        Task::STATUS_PAUSED => [Task::STATUS_IN_PROGRESS, Task::STATUS_COMPLETED, Task::STATUS_CANCELLED],
        Task::STATUS_DELAYED => [Task::STATUS_IN_PROGRESS, Task::STATUS_CANCELLED],
        Task::STATUS_UNABLE_TO_ACCESS => [Task::STATUS_IN_PROGRESS, Task::STATUS_CANCELLED],
        Task::STATUS_COMPLETED => [Task::STATUS_SUBMITTED_FOR_APPROVAL, Task::STATUS_APPROVED, Task::STATUS_REOPENED],
        Task::STATUS_SUBMITTED_FOR_APPROVAL => [Task::STATUS_APPROVED, Task::STATUS_REJECTED, Task::STATUS_CORRECTION_REQUESTED],
        Task::STATUS_CORRECTION_REQUESTED => [Task::STATUS_IN_PROGRESS, Task::STATUS_APPROVED],
        Task::STATUS_REJECTED => [Task::STATUS_REOPENED, Task::STATUS_CANCELLED],
        Task::STATUS_REOPENED => [Task::STATUS_IN_PROGRESS, Task::STATUS_CANCELLED],
        Task::STATUS_APPROVED => [],
        Task::STATUS_CANCELLED => [Task::STATUS_REOPENED],
    ];

    private const TIMESTAMP_COLUMNS = [
        Task::STATUS_ACCEPTED => 'accepted_at',
        Task::STATUS_IN_PROGRESS => 'started_at',
        Task::STATUS_COMPLETED => 'completed_at',
        Task::STATUS_SUBMITTED_FOR_APPROVAL => 'submitted_at',
        Task::STATUS_APPROVED => 'approved_at',
        Task::STATUS_REJECTED => 'rejected_at',
        Task::STATUS_CANCELLED => 'cancelled_at',
    ];

    private const NOTIFICATION_MAP = [
        'task.accepted' => ['Task accepted', 'You accepted {task}.'],
        'task.declined' => ['Task declined', '{assignee} declined {task}.'],
        'task.cancelled' => ['Task cancelled', '{task} was cancelled.'],
        'task.overdue' => ['Task overdue', '{task} is overdue.'],
        'task.submitted' => ['Task submitted', '{task} was submitted for approval.'],
        'task.correction_requested' => ['Correction requested', 'Correction requested on {task}.'],
        'task.approved' => ['Task approved', '{task} was approved.'],
        'task.rejected' => ['Task rejected', '{task} was rejected.'],
        'task.reopened' => ['Task reopened', '{task} was reopened.'],
    ];

    private const SUPERVISOR_NOTIFIED_STATUSES = [
        Task::STATUS_SUBMITTED_FOR_APPROVAL,
    ];

    public function __construct(
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array{remarks?: ?string, latitude?: ?float, longitude?: ?float, source?: string}  $context
     */
    public function transition(Task $task, string $newStatus, ?User $actor = null, array $context = []): Task
    {
        if ($task->status === $newStatus) {
            return $task;
        }

        if (! in_array($newStatus, self::TRANSITIONS[$task->status] ?? [], true)) {
            throw new \InvalidArgumentException("Invalid transition: {$task->status} → {$newStatus}.");
        }

        $this->assertAllowed($task, $newStatus, $actor);

        return DB::transaction(function () use ($task, $newStatus, $actor, $context): Task {
            $previous = $task->status;

            $updates = ['status' => $newStatus];

            if (isset(self::TIMESTAMP_COLUMNS[$newStatus])) {
                $updates[self::TIMESTAMP_COLUMNS[$newStatus]] = now();
            }

            if ($newStatus === Task::STATUS_DECLINED) {
                TaskAssignment::where('task_id', $task->id)
                    ->when($actor, fn ($q) => $q->where('assignee_type', 'user')->where('assignee_id', $actor->id))
                    ->update(['status' => TaskAssignment::STATUS_DECLINED]);
            }

            // Work-time accounting: pause/complete accumulates worked_seconds, resume records last_resume_at.
            if ($newStatus === Task::STATUS_PAUSED || $newStatus === Task::STATUS_COMPLETED) {
                $segmentStart = $task->last_resume_at ?? ($task->status === Task::STATUS_IN_PROGRESS ? $task->started_at : null);
                if ($segmentStart) {
                    $updates['worked_seconds'] = (int) $task->worked_seconds + abs(now()->diffInSeconds($segmentStart));
                }
                $updates['last_resume_at'] = null;
            } elseif ($newStatus === Task::STATUS_IN_PROGRESS) {
                $updates['last_resume_at'] = now();
            }

            $task->update($updates + ['updated_by' => $actor?->id]);

            $source = $context['source'] ?? (request()?->is('api/*') ? 'api' : 'web');

            TaskStatusHistory::create([
                'task_id' => $task->id,
                'previous_status' => $previous,
                'new_status' => $newStatus,
                'user_id' => $actor?->id,
                'remarks' => $context['remarks'] ?? null,
                'device' => request()?->userAgent(),
                'latitude' => $context['latitude'] ?? null,
                'longitude' => $context['longitude'] ?? null,
                'source' => $source,
            ]);

            $this->audit->log("task.{$newStatus}", 'task', $task->id, [
                'before' => ['status' => $previous],
                'after' => ['status' => $newStatus],
                'actor_id' => $actor?->id,
            ]);

            $this->notify($task, $newStatus);

            return $task->fresh();
        });
    }

    private function assertAllowed(Task $task, string $newStatus, ?User $actor): void
    {
        if (! $actor) {
            return;
        }

        $permission = match ($newStatus) {
            Task::STATUS_APPROVED, Task::STATUS_REJECTED, Task::STATUS_CORRECTION_REQUESTED => '4.5',
            Task::STATUS_CANCELLED, Task::STATUS_REOPENED => '4.6',
            default => '4.4',
        };

        if (! $actor->hasPermission($permission)) {
            throw new \DomainException("Missing permission {$permission} for {$newStatus}.");
        }

        // Cleaners may only act on their own assignments.
        if ($actor->hasRole(User::ROLE_CLEANER)) {
            $isAssignee = $task->assignments()
                ->where('assignee_type', 'user')
                ->where('assignee_id', $actor->id)
                ->exists();

            if (! $isAssignee) {
                throw new \DomainException('You are not assigned to this task.');
            }
        }

        // Nobody approves their own work.
        if (in_array($newStatus, [Task::STATUS_APPROVED, Task::STATUS_REJECTED], true)
            && $task->assignments()->where('assignee_type', 'user')->where('assignee_id', $actor->id)->exists()) {
            throw new \DomainException('You cannot approve or reject your own task.');
        }
    }

    private function notify(Task $task, string $newStatus): void
    {
        $template = self::NOTIFICATION_MAP["task.{$newStatus}"] ?? null;

        if ($template) {
            $title = str_replace('{task}', $task->title, $template[0]);
            $body = str_replace(['{task}', '{assignee}'], [$task->title, 'A staff member'], $template[1]);

            foreach ($task->assignments()->with('assignee')->get() as $assignment) {
                $assignee = $assignment->assignee;

                if ($assignee instanceof User) {
                    $this->notifications->send(
                        $assignee,
                        "task.{$newStatus}",
                        $title,
                        $body,
                        ['task_id' => $task->id, 'status' => $newStatus],
                        "task.{$newStatus}:{$task->id}:{$assignee->id}",
                    );
                }
            }
        }

        // Approval flow: the assigned supervisor/manager gets mail + notification.
        if (in_array($newStatus, self::SUPERVISOR_NOTIFIED_STATUSES, true)) {
            $supervisor = $task->assignedManager ?: $task->createdByUser;

            if ($supervisor && $supervisor instanceof User) {
                $this->notifications->send(
                    $supervisor,
                    "task.{$newStatus}",
                    'Task awaiting approval',
                    "{$task->title} was submitted for your approval.",
                    ['task_id' => $task->id, 'status' => $newStatus],
                    "task.{$newStatus}:supervisor:{$task->id}:{$supervisor->id}",
                    [\App\Models\NotificationDelivery::CHANNEL_IN_APP, \App\Models\NotificationDelivery::CHANNEL_EMAIL, \App\Models\NotificationDelivery::CHANNEL_PUSH],
                    new \App\Mail\TaskApprovalRequestedMail($task),
                );
            }
        }
    }
}
