<?php

namespace App\Http\Requests;

use App\Models\PropertyAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('3.6') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assignable_type' => ['required', Rule::in(['user', 'team', 'branch'])],
            'assignable_id' => ['required', 'integer'],
            'assignment_role' => ['required', Rule::in(PropertyAssignment::ROLES)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_primary' => ['sometimes', 'boolean'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
