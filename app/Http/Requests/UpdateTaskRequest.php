<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('4.3') ?? false;
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
            'scheduled_start_at' => ['nullable', 'date'],
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
            'subtasks' => ['nullable', 'array'],
            'subtasks.*.title' => ['required', 'string', 'max:255'],
        ];
    }
}
