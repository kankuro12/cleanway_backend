<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('4.2') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'], // auto-derived from property when blank
            'description' => ['nullable', 'string'],
            'task_type_id' => ['nullable', 'exists:task_types,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'property_name_snapshot' => ['nullable', 'string', 'max:255'],
            'address_snapshot' => ['nullable', 'string', 'max:500'],
            'latitude_snapshot' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude_snapshot' => ['nullable', 'numeric', 'between:-180,180'],
            'assigned_manager_id' => ['nullable', 'exists:users,id'],
            'scheduled_start_at' => ['required', 'date'],
            'scheduled_end_at' => ['nullable', 'date', 'after:scheduled_start_at'],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'duration_hours' => ['nullable', 'integer', 'min:0', 'max:24'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'parking_fee' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'extra_payments' => ['nullable', 'array'],
            'extra_payments.*.description' => ['nullable', 'string', 'max:255'],
            'extra_payments.*.amount' => ['nullable', 'numeric', 'min:0'],
            'priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'approval_required' => ['sometimes', 'boolean'],
            'recurrence_rule' => ['nullable', 'string', 'max:100'],
            'checklist_template_id' => ['nullable', 'exists:checklist_templates,id'],
            'assignee_type' => ['nullable', Rule::in(['user', 'team'])],
            'assignee_id' => ['required_with:assignee_type', 'integer'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
            'override_warnings' => ['sometimes', 'boolean'],
            'override_reason' => ['nullable', 'required_if:override_warnings,1', 'string', 'max:500'],
        ];
    }
}
