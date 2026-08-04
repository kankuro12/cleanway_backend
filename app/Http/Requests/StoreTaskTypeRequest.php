<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('4.7') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default_estimated_duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'default_priority' => ['required', Rule::in(['low', 'medium', 'high', 'critical'])],
            'default_instructions' => ['nullable', 'string'],
            'default_checklist_id' => ['nullable', 'exists:checklist_templates,id'],
            'before_photo_required' => ['sometimes', 'boolean'],
            'after_photo_required' => ['sometimes', 'boolean'],
            'minimum_photo_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'approval_required' => ['sometimes', 'boolean'],
            'allowed_assignee_types' => ['nullable', 'array'],
            'allowed_assignee_types.*' => [Rule::in(['user', 'team'])],
            'active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
