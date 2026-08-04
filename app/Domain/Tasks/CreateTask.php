<?php

namespace App\Domain\Tasks;

use App\Models\ChecklistTemplate;
use App\Models\Property;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskChecklistSnapshot;
use App\Models\TaskSubtask;
use App\Models\TaskType;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Geocoding\EffectiveRadiusResolver;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;

class CreateTask
{
    public function __construct(
        private readonly TaskSchedulingValidator $validator,
        private readonly AssignTask $assignTask,
        private readonly NotificationService $notifications,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @return array{task: Task, warnings: array<int, string>}
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function execute(array $data, ?User $actor = null, ?TaskType $taskType = null, ?ChecklistTemplate $checklistTemplate = null): array
    {
        $warnings = [];

        return DB::transaction(function () use ($data, $actor, $taskType, $checklistTemplate, &$warnings): array {
            $task = $this->createWithSnapshots($data, $actor, $taskType, $checklistTemplate);

            // Multiple people + optional team (spec §9.1: one or many cleaners).
            $assigneeIds = $data['assignee_ids'] ?? [];
            if (empty($assigneeIds) && ! empty($data['assignee_type']) && ! empty($data['assignee_id'])) {
                $assigneeIds = [(int) $data['assignee_id']];
            }

            foreach ($assigneeIds as $assigneeId) {
                $result = $this->assignTask->execute($task, 'user', (int) $assigneeId, $actor);
                $warnings = array_merge($warnings, $result['warnings']);
            }

            if (! empty($data['team_id'])) {
                $result = $this->assignTask->execute($task, 'team', (int) $data['team_id'], $actor);
                $warnings = array_merge($warnings, $result['warnings']);
            }

            foreach ($data['subtasks'] ?? [] as $index => $subtask) {
                TaskSubtask::create([
                    'task_id' => $task->id,
                    'title' => $subtask['title'],
                    'sort_order' => $index,
                ]);
            }

            $this->audit->log('task.created', 'task', $task->id, [
                'after' => [
                    'title' => $task->title,
                    'status' => $task->status,
                    'scheduled_start_at' => $task->scheduled_start_at?->toIso8601String(),
                ],
                'actor_id' => $actor->id,
            ]);

            return ['task' => $task->load('assignments', 'checklistSnapshot', 'subtasks'), 'warnings' => $warnings];
        });
    }

    private function createWithSnapshots(array $data, User $actor, ?TaskType $taskType, ?ChecklistTemplate $checklistTemplate): Task
    {
        $taskType ??= isset($data['task_type_id']) ? TaskType::find($data['task_type_id']) : null;
        $checklistTemplate ??= isset($data['checklist_template_id']) ? ChecklistTemplate::find($data['checklist_template_id']) : null;
        $checklistTemplate ??= $taskType?->defaultChecklist;
        $property = isset($data['property_id']) ? Property::find($data['property_id']) : null;

        $scheduledStart = isset($data['scheduled_start_at']) ? \Carbon\Carbon::parse($data['scheduled_start_at']) : null;
        $duration = $data['estimated_duration_minutes'] ?? $taskType?->default_estimated_duration_minutes;
        $propertyName = $data['property_name_snapshot'] ?? $property?->name;

        $assigneeIds = $data['assignee_ids'] ?? [];
        if (empty($assigneeIds) && ! empty($data['assignee_id'])) {
            $assigneeIds = [(int) $data['assignee_id']];
        }
        $hasAssignee = ! empty($assigneeIds) || ! empty($data['team_id']);

        $task = Task::create([
            // Title is optional in the UI — derive from the property/location when blank.
            'title' => ($data['title'] ?? null) ?: ($propertyName ?: ($taskType?->name ?? 'Task')),
            'description' => $data['description'] ?? null,
            'task_type_id' => $taskType?->id,
            'property_id' => $property?->id,
            'property_name_snapshot' => $propertyName,
            'address_snapshot' => $data['address_snapshot'] ?? $property?->formatted_address ?: $property?->address,
            'latitude_snapshot' => $data['latitude_snapshot'] ?? $property?->latitude,
            'longitude_snapshot' => $data['longitude_snapshot'] ?? $property?->longitude,
            'check_in_radius_snapshot' => $data['check_in_radius_snapshot'] ?? ($property ? app(EffectiveRadiusResolver::class)->resolve($property) : null),
            'assigned_manager_id' => $data['assigned_manager_id'] ?? null,
            'scheduled_start_at' => $scheduledStart,
            'scheduled_end_at' => isset($data['scheduled_end_at'])
                ? \Carbon\Carbon::parse($data['scheduled_end_at'])
                : ($scheduledStart?->copy()->addMinutes($duration ?: 60)),
            'estimated_duration_minutes' => $duration,
            'priority' => $data['priority'] ?? $taskType?->default_priority ?? 'medium',
            'status' => $hasAssignee ? Task::STATUS_ASSIGNED : Task::STATUS_SCHEDULED,
            'recurrence_rule' => $data['recurrence_rule'] ?? null,
            'approval_required' => (bool) ($data['approval_required'] ?? $taskType?->approval_required ?? false),
            'task_type_snapshot' => $taskType ? $this->snapshotTaskType($taskType) : null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->snapshotChecklist($task, $checklistTemplate);

        return $task;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotTaskType(TaskType $taskType): array
    {
        return [
            'name' => $taskType->name,
            'default_instructions' => $taskType->default_instructions,
            'before_photo_required' => $taskType->before_photo_required,
            'after_photo_required' => $taskType->after_photo_required,
            'minimum_photo_count' => $taskType->minimum_photo_count,
            'approval_required' => $taskType->approval_required,
        ];
    }

    private function snapshotChecklist(Task $task, ?ChecklistTemplate $template): void
    {
        if (! $template) {
            return;
        }

        $rows = [];
        $order = 0;

        foreach ($template->sections()->with('items')->get() as $section) {
            foreach ($section->items as $item) {
                $rows[] = [
                    'section_name' => $section->name,
                    'item_label' => $item->label,
                    'item_type' => $item->item_type,
                    'required' => $item->required,
                    'issue_triggering' => $item->issue_triggering,
                    'sort_order' => $order++,
                ];
            }
        }

        if ($rows) {
            foreach ($rows as $row) {
                TaskChecklistSnapshot::create($row + ['task_id' => $task->id]);
            }
        }
    }
}
