<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRecurrenceRequest extends FormRequest
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
            'rule' => ['required', 'string', 'max:100', 'regex:/^FREQ=(DAILY|WEEKLY|MONTHLY|YEARLY)(;INTERVAL=\d+)?$/'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'time' => ['required', 'date_format:H:i'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'assignee_type' => ['nullable', Rule::in(['user', 'team'])],
            'assignee_id' => ['required_with:assignee_type', 'integer'],
            'task_type_id' => ['nullable', 'exists:task_types,id'],
            'checklist_template_id' => ['nullable', 'exists:checklist_templates,id'],
            'notification_minutes_before' => ['nullable', 'integer', 'min:0', 'max:10080'],
        ];
    }
}
