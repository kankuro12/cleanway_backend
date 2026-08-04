<?php

namespace App\Http\Requests;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('3.3') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'property_category_id' => ['nullable', 'exists:property_categories,id'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'access_instructions' => ['nullable', 'string'],
            'parking_instructions' => ['nullable', 'string'],
            'safety_instructions' => ['nullable', 'string'],
            'special_cleaning_requirements' => ['nullable', 'string'],
            'service_frequency' => ['nullable', 'string', 'max:30'],
            'permitted_check_in_radius_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'active' => ['sometimes', 'boolean'],
            'internal_notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:property_tags,id'],
            'geocode_status' => ['nullable', Rule::in(Property::GEOCODE_STATUSES)],
        ];
    }
}
