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
            'property_code' => ['nullable', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'formatted_address' => ['nullable', 'string', 'max:500'],
            'google_place_id' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'property_category_id' => ['nullable', 'exists:property_categories,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'access_instructions' => ['nullable', 'string'],
            'parking_instructions' => ['nullable', 'string'],
            'safety_instructions' => ['nullable', 'string'],
            'special_cleaning_requirements' => ['nullable', 'string'],
            'service_frequency' => ['nullable', 'string', 'max:30'],
            'bedrooms_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'bathrooms_count' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'parking_type' => ['nullable', 'string', 'max:50'],
            'parking_spaces_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'permitted_check_in_radius_meters' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'needs_parking' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
            'internal_notes' => ['nullable', 'string'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:property_tags,id'],
            'geocode_status' => ['nullable', Rule::in(Property::GEOCODE_STATUSES)],
            'cleaning_duration_hours' => ['nullable', 'integer', 'min:0', 'max:240'],
            'cleaning_duration_minutes' => ['nullable', 'integer', 'min:0', 'max:59'],
            'client_fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'cleaner_pay_type' => ['nullable', Rule::in(['fixed', 'per_hour'])],
            'cleaner_fixed_amount' => ['nullable', 'numeric', 'min:0'],
            'cleaner_rate_per_hour' => ['nullable', 'numeric', 'min:0'],
            'parking_fee' => ['nullable', 'numeric', 'min:0'],
            'beds' => ['nullable', 'array'],
            'beds.*.bed_type_id' => ['nullable', 'exists:bed_types,id'],
            'beds.*.quantity' => ['nullable', 'integer', 'min:0', 'max:100'],
            'beds.*.room_name' => ['nullable', 'string', 'max:100'],
            'linens' => ['nullable', 'array'],
            'linens.*.linen_type_id' => ['nullable', 'exists:linen_types,id'],
            'linens.*.quantity' => ['nullable', 'integer', 'min:0', 'max:500'],
            'linens.*.custom_rate' => ['nullable', 'numeric', 'min:0'],
            'linens.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
