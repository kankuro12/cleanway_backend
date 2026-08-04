<?php

namespace App\Domain\Tasks;

use App\Models\Task;
use App\Models\TaskApproval;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Approval actions (spec §13.3): approve/reject/request_correction/reopen.
 * Every action records a task_approvals row and drives the state machine.
 * A cleaner can never review their own task (enforced in TransitionTaskStatus).
 *
 * @param  array<string, mixed>  $meta
 */
class TaskApprovalActions
{
    public function __construct(
        private readonly TransitionTaskStatus $transitioner,
        private readonly AuditLogger $audit,
    ) {}

    public function approve(Task $task, User $reviewer, array $meta = []): Task
    {
        return $this->recordAndTransition($task, $reviewer, TaskApproval::ACTION_APPROVE, Task::STATUS_APPROVED, $meta);
    }

    public function reject(Task $task, User $reviewer, array $meta = []): Task
    {
        return $this->recordAndTransition($task, $reviewer, TaskApproval::ACTION_REJECT, Task::STATUS_REJECTED, $meta);
    }

    public function requestCorrection(Task $task, User $reviewer, array $meta = []): Task
    {
        return $this->recordAndTransition($task, $reviewer, TaskApproval::ACTION_REQUEST_CORRECTION, Task::STATUS_CORRECTION_REQUESTED, $meta);
    }

    public function reopen(Task $task, User $reviewer, array $meta = []): Task
    {
        return $this->recordAndTransition($task, $reviewer, TaskApproval::ACTION_REOPEN, Task::STATUS_REOPENED, $meta);
    }

    private function recordAndTransition(Task $task, User $reviewer, string $action, string $status, array $meta): Task
    {
        return DB::transaction(function () use ($task, $reviewer, $action, $status, $meta): Task {
            TaskApproval::create([
                'task_id' => $task->id,
                'action' => $action,
                'reviewer_id' => $reviewer->id,
                'previous_status' => $task->status,
                'remarks' => $meta['remarks'] ?? null,
                'reason_code' => $meta['reason_code'] ?? null,
                'requested_corrections' => $meta['requested_corrections'] ?? null,
                'quality_score' => $meta['quality_score'] ?? null,
            ]);

            $updated = $this->transitioner->transition($task, $status, $reviewer, [
                'remarks' => $meta['remarks'] ?? null,
                'source' => $meta['source'] ?? 'web',
            ]);

            $this->audit->log("task.{$action}", 'task', $task->id, [
                'after' => ['status' => $status, 'action' => $action],
                'actor_id' => $reviewer->id,
            ]);

            return $updated;
        });
    }
}
