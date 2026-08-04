<?php

namespace App\Domain\Tasks;

use App\Models\Task;
use App\Models\TaskRecurrence;
use App\Models\User;
use Carbon\CarbonImmutable;

/**
 * Generates task instances from recurrence templates ahead of time (spec §9.4).
 * Supports RRULE-style strings: FREQ=DAILY|WEEKLY|MONTHLY|YEARLY, INTERVAL=n.
 * Completed instances are never modified by template changes — each generated
 * task is a fresh row carrying its own snapshots.
 */
class GenerateRecurringTasks
{
    public function __construct(private readonly CreateTask $createTask) {}

    /**
     * Generate instances from now until $horizon (default 30 days out).
     *
     * @return int number of tasks created
     */
    public function generate(TaskRecurrence $recurrence, int $horizonDays = 30, ?User $actor = null): int
    {
        if (! $recurrence->active) {
            return 0;
        }

        $rule = $this->parseRule($recurrence->rule);
        $start = CarbonImmutable::parse($recurrence->start_date->format('Y-m-d').' '.$recurrence->time);
        $horizon = now()->addDays($horizonDays);
        $end = $recurrence->end_date ? CarbonImmutable::parse($recurrence->end_date->format('Y-m-d').' 23:59:59') : $horizon;
        $created = 0;

        $interval = max(1, $rule['interval'] ?? 1);

        for ($date = $start; $date->lte($end) && $date->lte($horizon); $date = $this->nextOccurrence($date, $rule['freq'], $interval)) {
            if ($date->lt(now())) {
                continue;
            }

            // Skip a day that already has an instance for this recurrence.
            $exists = Task::where('recurrence_rule', $recurrence->rule)
                ->where('scheduled_start_at', '>=', $date->toDateTimeString())
                ->where('scheduled_start_at', '<', $date->addMinute()->toDateTimeString())
                ->exists();

            if ($exists) {
                continue;
            }

            $data = [
                'title' => ($recurrence->property?->name ?: 'Recurring').' task',
                'task_type_id' => $recurrence->task_type_id,
                'property_id' => $recurrence->property_id,
                'scheduled_start_at' => $date->toDateTimeString(),
                'assignee_type' => $recurrence->assignee_type,
                'assignee_id' => $recurrence->assignee_id,
                'recurrence_rule' => $recurrence->rule,
                'checklist_template_id' => $recurrence->checklist_template_id,
            ];

            $this->createTask->execute($data, $actor, null, $recurrence->checklistTemplate);
            $created++;
        }

        return $created;
    }

    /**
     * @return array{freq: string, interval: int}
     */
    private function parseRule(string $rule): array
    {
        $freq = 'WEEKLY';
        $interval = 1;

        if (preg_match('/FREQ=([A-Z]+)/', $rule, $m)) {
            $freq = $m[1];
        }

        if (preg_match('/INTERVAL=(\d+)/', $rule, $m)) {
            $interval = (int) $m[1];
        }

        return ['freq' => $freq, 'interval' => $interval];
    }

    private function nextOccurrence(CarbonImmutable $date, string $freq, int $interval): CarbonImmutable
    {
        return match ($freq) {
            'DAILY' => $date->addDays($interval),
            'MONTHLY' => $date->addMonths($interval),
            'YEARLY' => $date->addYears($interval),
            default => $date->addWeeks($interval),
        };
    }
}
