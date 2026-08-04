<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'reference_number' => $this->reference_number,
            'title' => $this->title,
            'description' => $this->description,
            'task_type_id' => $this->task_type_id,
            'task_type' => $this->whenLoaded('taskType', fn () => ['id' => $this->taskType->id, 'name' => $this->taskType->name]),
            'property_id' => $this->property_id,
            'property' => $this->whenLoaded('property', fn () => ['id' => $this->property->id, 'name' => $this->property->name]),
            'location' => [
                'property_name_snapshot' => $this->property_name_snapshot,
                'address_snapshot' => $this->address_snapshot,
                'latitude' => $this->latitude_snapshot,
                'longitude' => $this->longitude_snapshot,
                'check_in_radius_meters' => $this->check_in_radius_snapshot,
            ],
            'scheduled_start_at' => $this->scheduled_start_at?->toIso8601String(),
            'scheduled_end_at' => $this->scheduled_end_at?->toIso8601String(),
            'estimated_duration_minutes' => $this->estimated_duration_minutes,
            'priority' => $this->priority,
            'status' => $this->status,
            'approval_required' => (bool) $this->approval_required,
            'task_type_snapshot' => $this->task_type_snapshot,
            'checklist' => $this->whenLoaded('checklistSnapshot', fn () => $this->checklistSnapshot->map(fn ($item) => [
                'section' => $item->section_name,
                'label' => $item->item_label,
                'type' => $item->item_type,
                'required' => (bool) $item->required,
            ])),
            'assignments' => $this->whenLoaded('assignments', fn () => $this->assignments->map(fn ($a) => [
                'id' => $a->id,
                'assignee_type' => $a->assignee_type,
                'assignee_id' => $a->assignee_id,
                'assignee' => $a->assignee ? ['id' => $a->assignee->id, 'name' => $a->assignee->name] : null,
                'status' => $a->status,
            ])),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'approved_at' => $this->approved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
