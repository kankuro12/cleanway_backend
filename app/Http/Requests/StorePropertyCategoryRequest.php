<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('3.4') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default_check_in_radius_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'default_manager_id' => ['nullable', 'exists:users,id'],
            'default_team_id' => ['nullable', 'exists:teams,id'],
            'default_safety_instructions' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
