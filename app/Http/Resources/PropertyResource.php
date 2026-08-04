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
            'property_category_id' => $this->property_category_id,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'contact_name' => $this->contact_name,
            'contact_phone' => $this->contact_phone,
            'contact_email' => $this->contact_email,
            'postal_code' => $this->postal_code,
            'access_instructions' => $this->access_instructions,
            'parking_instructions' => $this->parking_instructions,
            'safety_instructions' => $this->safety_instructions,
            'service_frequency' => $this->service_frequency,
            'active' => (bool) $this->active,
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
