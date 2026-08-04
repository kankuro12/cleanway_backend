<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonnelRequest extends FormRequest
{
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'role' => ['sometimes', Rule::in(User::ROLES)],
            'status' => ['sometimes', Rule::in(User::STATUSES)],
            'employee_no' => ['sometimes', 'nullable', 'string', 'max:50', Rule::unique('users', 'employee_no')->ignore($userId)],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'branch_id' => ['sometimes', 'nullable', 'exists:branches,id'],
            'team_id' => ['sometimes', 'nullable', 'exists:teams,id'],
            'manager_id' => ['sometimes', 'nullable', 'exists:users,id'],
            'employment_type' => ['sometimes', 'nullable', 'string', 'max:30'],
            'start_date' => ['sometimes', 'nullable', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
            'emergency_contact' => ['sometimes', 'nullable', 'array'],
            'skills' => ['sometimes', 'nullable', 'array'],
            'certifications' => ['sometimes', 'nullable', 'array'],
            'default_working_hours' => ['sometimes', 'nullable', 'array'],
            'service_areas' => ['sometimes', 'nullable', 'array'],
            'notification_preferences' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
