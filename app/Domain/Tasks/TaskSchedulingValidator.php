<?php

namespace App\Domain\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Scheduling validation for task creation/assignment: conflicts, availability,
 * leave, skills. Returns structured results so callers can surface warnings
 * with an override reason (spec §9.3).
 */
class TaskSchedulingValidator
{
    /**
     * @return array{errors: array<int, string>, warnings: array<int, string>}
     */
    public function validate(Task $task, ?User $assignee = null): array
    {
        $errors = [];
        $warnings = [];
        $assignee ??= null;

        if ($task->scheduled_start_at && $task->scheduled_end_at && $task->scheduled_end_at->lte($task->scheduled_start_at)) {
            $errors[] = 'Scheduled end must be after scheduled start.';
        }

        if ($assignee) {
            $this->checkAvailability($assignee, $errors, $warnings);
            $this->checkLeave($assignee, $task, $errors, $warnings);
            $this->checkSkills($assignee, $task, $warnings);
            $this->checkConflicts($assignee, $task, $warnings);
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    private function checkAvailability(User $assignee, array &$errors, array &$warnings): void
    {
        if ($assignee->status !== User::STATUS_ACTIVE) {
            $errors[] = "{$assignee->name} is not active (status: {$assignee->status}).";
        }

        if ($assignee->end_date && $assignee->end_date->isPast()) {
            $errors[] = "{$assignee->name} is past their employment end date.";
        }
    }

    private function checkLeave(User $assignee, Task $task, array &$errors, array &$warnings): void
    {
        if (! $task->scheduled_start_at) {
            return;
        }

        if ($assignee->start_date && $task->scheduled_start_at->lt($assignee->start_date)) {
            $errors[] = "{$assignee->name} starts employment on {$assignee->start_date->toDateString()}.";
        }

        if ($assignee->end_date && $task->scheduled_start_at->gt($assignee->end_date)) {
            $errors[] = "{$assignee->name} employment ends on {$assignee->end_date->toDateString()}.";
        }
    }

    private function checkSkills(User $assignee, Task $task, array &$warnings): void
    {
        $skills = $assignee->skills ?? [];

        if (empty($skills) || ! $task->taskType) {
            return;
        }

        // Task types do not declare required skills yet — flag only when the
        // assignee explicitly lists none at all.
        if (empty($skills)) {
            $warnings[] = "{$assignee->name} has no listed skills.";
        }
    }

    private function checkConflicts(User $assignee, Task $task, array &$warnings): void
    {
        if (! $task->scheduled_start_at || ! $task->scheduled_end_at) {
            return;
        }

        $overlap = Task::query()
            ->whereKeyNot($task->id)
            ->where('status', '!=', Task::STATUS_CANCELLED)
            ->whereHas('assignments', fn ($q) => $q->where('assignee_type', 'user')->where('assignee_id', $assignee->id))
            ->where('scheduled_start_at', '<', $task->scheduled_end_at)
            ->where('scheduled_end_at', '>', $task->scheduled_start_at)
            ->exists();

        if ($overlap) {
            $warnings[] = "{$assignee->name} has an overlapping task in this time window.";
        }
    }
}
