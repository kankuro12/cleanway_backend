<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'address' => $this->address,
            'formatted_address' => $this->formatted_address,
            'google_place_id' => $this->google_place_id,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'geocode_accuracy' => $this->geocode_accuracy,
            'geocode_status' => $this->geocode_status,
            'geocoded_at' => $this->geocoded_at?->toIso8601String(),
            'location_source' => $this->location_source,
            'permitted_check_in_radius_meters' => $this->permitted_check_in_radius_meters,
            'needs_parking' => (bool) $this->needs_parking,
            'property_category_id' => $this->property_category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'client_id' => $this->client_id,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
                'company_name' => $this->client->company_name,
            ]),
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'postal_code' => $this->postal_code,
            'access_instructions' => $this->access_instructions,
            'parking_instructions' => $this->parking_instructions,
            'safety_instructions' => $this->safety_instructions,
            'service_frequency' => $this->service_frequency,
            'bedrooms_count' => (int) ($this->bedrooms_count ?? 0),
            'bathrooms_count' => (float) ($this->bathrooms_count ?? 1.0),
            'parking_type' => $this->parking_type ?? 'none',
            'parking_spaces_count' => (int) ($this->parking_spaces_count ?? 0),
            'cleaning_duration_minutes' => $this->cleaning_duration_minutes,
            'client_fixed_amount' => $this->client_fixed_amount !== null ? (float) $this->client_fixed_amount : null,
            'cleaner_pay_type' => $this->cleaner_pay_type,
            'cleaner_fixed_amount' => $this->cleaner_fixed_amount !== null ? (float) $this->cleaner_fixed_amount : null,
            'cleaner_rate_per_hour' => $this->cleaner_rate_per_hour !== null ? (float) $this->cleaner_rate_per_hour : null,
            'parking_fee' => (float) ($this->parking_fee ?? 0),
            'active' => (bool) $this->active,
            'beds' => $this->whenLoaded('beds', fn () => $this->beds->map(fn ($bed) => [
                'id' => $bed->id,
                'bed_type_id' => $bed->bed_type_id,
                'bed_type_name' => $bed->bedType?->name,
                'quantity' => $bed->quantity,
                'room_name' => $bed->room_name,
            ])),
            'linens' => $this->whenLoaded('linens', fn () => $this->linens->map(fn ($linen) => [
                'id' => $linen->id,
                'linen_type_id' => $linen->linen_type_id,
                'linen_type_name' => $linen->linenType?->name,
                'standard_rate' => (float) ($linen->linenType?->rate ?? 0),
                'quantity' => $linen->quantity,
                'custom_rate' => $linen->custom_rate !== null ? (float) $linen->custom_rate : null,
                'effective_rate' => (float) $linen->effective_rate,
                'total_cost' => (float) $linen->total_cost,
                'notes' => $linen->notes,
            ])),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'color' => $tag->color,
            ])),
            'assignments' => $this->whenLoaded('assignments', fn () => $this->assignments->map(fn ($a) => [
                'id' => $a->id,
                'assignable_type' => $a->assignable_type,
                'assignable_id' => $a->assignable_id,
                'assignment_role' => $a->assignment_role,
                'start_date' => $a->start_date?->toDateString(),
                'end_date' => $a->end_date?->toDateString(),
                'is_primary' => (bool) $a->is_primary,
                'assignable' => $a->assignable ? ['id' => $a->assignable->id, 'name' => $a->assignable->name] : null,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
