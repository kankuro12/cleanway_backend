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
                $title = is_array($subtask) ? ($subtask['title'] ?? '') : (string) $subtask;
                $section = (is_array($subtask) && ! empty($subtask['section_name'])) ? $subtask['section_name'] : 'Other';
                if (! empty($title)) {
                    TaskSubtask::create([
                        'task_id' => $task->id,
                        'title' => $title,
                        'section_name' => $section,
                        'sort_order' => $index,
                    ]);

                    TaskChecklistSnapshot::create([
                        'task_id' => $task->id,
                        'section_name' => $section,
                        'item_label' => $title,
                        'item_type' => 'pass_fail',
                        'required' => false,
                        'sort_order' => 100 + $index,
                    ]);
                }
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
        $duration = (int) ($data['estimated_duration_minutes'] ?? $taskType?->default_estimated_duration_minutes ?? $property?->cleaning_duration_minutes);
        if (isset($data['duration_hours']) || isset($data['duration_minutes'])) {
            $h = (int) ($data['duration_hours'] ?? 0);
            $m = (int) ($data['duration_minutes'] ?? 0);
            $calculatedDuration = ($h * 60) + $m;
            if ($calculatedDuration > 0) {
                $duration = $calculatedDuration;
            }
        }
        $propertyName = $data['property_name_snapshot'] ?? $property?->name;

        $assigneeIds = $data['assignee_ids'] ?? [];
        if (empty($assigneeIds) && ! empty($data['assignee_id'])) {
            $assigneeIds = [(int) $data['assignee_id']];
        }
        $hasAssignee = ! empty($assigneeIds) || ! empty($data['team_id']);

        $cleanerRate = isset($data['hourly_rate']) ? (float) $data['hourly_rate'] : null;
        if ($cleanerRate === null && $property && $property->cleaner_pay_type === 'per_hour') {
            $cleanerRate = $property->cleaner_rate_per_hour;
        }
        $parkingFee = isset($data['parking_fee']) ? (float) $data['parking_fee'] : ($property?->parking_fee ?? 0.00);

        $task = Task::create([
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
                : null,
            'estimated_duration_minutes' => $duration,
            'hourly_rate' => $cleanerRate,
            'parking_fee' => $parkingFee,
            'extra_payments' => isset($data['extra_payments']) ? (array) $data['extra_payments'] : $this->propertyExtraPayments($property),
            'priority' => $data['priority'] ?? $taskType?->default_priority ?? 'medium',
            'status' => $hasAssignee ? Task::STATUS_ASSIGNED : Task::STATUS_SCHEDULED,
            'recurrence_rule' => $data['recurrence_rule'] ?? null,
            'approval_required' => (bool) ($data['approval_required'] ?? $taskType?->approval_required ?? true),
            'task_type_snapshot' => $taskType ? $this->snapshotTaskType($taskType) : null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);

        $this->snapshotChecklist($task, $checklistTemplate, $data);

        return $task;
    }

    /**
     * Carry the property's billing defaults onto the task (client + cleaner side).
     *
     * @return array<string, mixed>|null
     */
    private function propertyExtraPayments(?Property $property): ?array
    {
        if (! $property) {
            return null;
        }

        $extra = [];
        if ($property->client_fixed_amount !== null) {
            $extra['client_fixed_amount'] = (float) $property->client_fixed_amount;
        }
        if ($property->cleaner_pay_type === 'fixed' && $property->cleaner_fixed_amount !== null) {
            $extra['cleaner_fixed_amount'] = (float) $property->cleaner_fixed_amount;
        }
        if ($property->parking_fee) {
            $extra['parking_fee'] = (float) $property->parking_fee;
        }

        return $extra ?: null;
    }

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

    private function snapshotChecklist(Task $task, ?ChecklistTemplate $template, array $data = []): void
    {
        $checklistTemplate = $template ?: (isset($data['checklist_template_id']) ? ChecklistTemplate::find($data['checklist_template_id']) : null);
        // Fallback: task type default
        if (! $checklistTemplate && isset($data['task_type_id'])) {
            $checklistTemplate = \App\Models\TaskType::find($data['task_type_id'])?->defaultChecklist;
        }

        $rows = [];
        $order = 0;

        if ($checklistTemplate) {
            foreach ($checklistTemplate->sections()->with('items')->get() as $section) {
                foreach ($section->items as $item) {
                    $rows[] = [
                        'section_name' => $section->name,
                        'item_label' => $item->label,
                        'item_type' => $item->item_type,
                        'required' => $item->required,
                        'is_photo_required' => (bool) ($item->is_photo_required ?? false),
                        'is_comment_required' => (bool) ($item->is_comment_required ?? false),
                        'issue_triggering' => $item->issue_triggering,
                        'sort_order' => $order++,
                    ];
                    // Each checklist item becomes a subtask
                    TaskSubtask::create([
                        'task_id' => $task->id,
                        'title' => $item->label,
                        'section_name' => $section->name,
                        'sort_order' => $order,
                    ]);
                }
            }
        }

        $property = $task->property;
        if ($property) {
            if (! empty($property->access_instructions)) {
                $rows[] = [
                    'section_name' => 'Property Specific',
                    'item_label' => 'Access: '.$property->access_instructions,
                    'item_type' => 'pass_fail',
                    'required' => false,
                    'is_photo_required' => false,
                    'is_comment_required' => false,
                    'issue_triggering' => false,
                    'sort_order' => $order++,
                ];
            }
            if (! empty($property->parking_instructions) && $property->needs_parking) {
                $rows[] = [
                    'section_name' => 'Property Specific',
                    'item_label' => 'Parking: '.$property->parking_instructions,
                    'item_type' => 'pass_fail',
                    'required' => false,
                    'is_photo_required' => false,
                    'is_comment_required' => false,
                    'issue_triggering' => false,
                    'sort_order' => $order++,
                ];
            }
        }

        if (empty($rows)) {
            $defaultData = [
                'Property Specific' => [
                    ['label' => 'DO NOT use any abrasive sponges or brushes on any surface in the kitchen and bathroom. It will damage the ceramic coating.', 'photo' => false, 'comment' => false],
                    ['label' => 'Gate code: PIN, 888888, OK. Front door keypad lock test.', 'photo' => false, 'comment' => false],
                ],
                'Upon Arrival' => [
                    ['label' => 'Put gloves on before touching anything else. Strip bed(s) and check bed bug protector.', 'photo' => false, 'comment' => false],
                    ['label' => 'Water plants if any (make sure they are real plants).', 'photo' => false, 'comment' => false],
                ],
                'Keys' => [
                    ['label' => 'How many keys are in the apartment? Take a photo', 'photo' => false, 'comment' => false],
                ],
                'Sleeper Sofa' => [
                    ['label' => 'Take a photo of the sofa bed and extra linens left', 'photo' => false, 'comment' => false],
                ],
            ];

            foreach ($defaultData as $sec => $items) {
                foreach ($items as $item) {
                    $rows[] = [
                        'section_name' => $sec,
                        'item_label' => $item['label'],
                        'item_type' => 'pass_fail',
                        'required' => false,
                        'is_photo_required' => $item['photo'],
                        'is_comment_required' => $item['comment'],
                        'issue_triggering' => false,
                        'sort_order' => $order++,
                    ];
                }
            }
        }

        foreach ($rows as $row) {
            TaskChecklistSnapshot::create($row + ['task_id' => $task->id]);
        }
    }
}
