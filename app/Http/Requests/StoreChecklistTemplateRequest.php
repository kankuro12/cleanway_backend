<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChecklistTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('4.8') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
            'sections' => ['nullable', 'array'],
            'sections.*.name' => ['required', 'string', 'max:255'],
            'sections.*.items' => ['nullable', 'array'],
            'sections.*.items.*.label' => ['required', 'string', 'max:255'],
            'sections.*.items.*.item_type' => ['required', 'in:yes_no,pass_fail,text,numeric,photo'],
            'sections.*.items.*.required' => ['sometimes', 'boolean'],
            'sections.*.items.*.issue_triggering' => ['sometimes', 'boolean'],
        ];
    }
}
