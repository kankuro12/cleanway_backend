<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Properties\CreateProperty;
use App\Domain\Properties\RetryPropertyGeocode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyTag;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $properties = Property::query()
            ->with(['category:id,name', 'tags:id,name,color'])
            ->filter($request->only(['search', 'active', 'category_id', 'geocode_status', 'missing_coords', 'unassigned', 'tag_id', 'assigned_to']))
            ->orderByDesc('updated_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => PropertyResource::collection($properties),
            'meta' => ['pagination' => [
                'total' => $properties->total(),
                'per_page' => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
            ]],
        ]);
    }

    public function show(Property $property): JsonResponse
    {
        $property->load(['category', 'tags', 'assignments.assignable']);

        return response()->json(['data' => new PropertyResource($property)]);
    }

    public function store(StorePropertyRequest $request, CreateProperty $createProperty, \App\Support\PropertyDetails $details): JsonResponse
    {
        $property = $createProperty->execute($request->safe()->except(['tags']), $request->user());

        if ($request->filled('tags')) {
            $property->tags()->sync($request->input('tags'));
        }

        $details->save($property, $request->safe()->toArray());

        return response()->json(['data' => new PropertyResource($property->fresh('category', 'tags'))], 201);
    }

    public function update(UpdatePropertyRequest $request, Property $property, AuditLogger $audit, \App\Support\PropertyDetails $details): JsonResponse
    {
        DB::transaction(function () use ($request, $property, $audit): void {
            $property->update($request->safe()->except(['tags']) + ['updated_by' => $request->user()->id]);

            if ($request->has('tags')) {
                $property->tags()->sync($request->input('tags'));
            }

            $audit->log('property.updated', 'property', $property->id);
        });

        $details->save($property, $request->safe()->toArray());

        return response()->json(['data' => new PropertyResource($property->fresh('category', 'tags'))]);
    }

    public function destroy(Request $request, Property $property, AuditLogger $audit): JsonResponse
    {
        DB::transaction(function () use ($property, $audit): void {
            $property->update(['active' => false]);
            $property->delete();

            $audit->log('property.archived', 'property', $property->id);
        });

        return response()->json(['data' => null]);
    }

    public function retryGeocode(Request $request, Property $property, RetryPropertyGeocode $retry): JsonResponse
    {
        $property = $retry->execute($property, $request->float('latitude'), $request->float('longitude'));

        return response()->json(['data' => new PropertyResource($property)]);
    }

    public function search(Request $request): JsonResponse
    {
        $properties = Property::query()
            ->with(['category:id,name', 'tags:id,name,color'])
            ->filter($request->only(['search', 'active', 'category_id', 'geocode_status', 'missing_coords', 'unassigned', 'tag_id', 'assigned_to']))
            ->limit(min($request->integer('limit', 10), 50))
            ->get(['id', 'uuid', 'name', 'address', 'formatted_address', 'latitude', 'longitude']);

        return response()->json(['data' => $properties]);
    }

    public function categories(): JsonResponse
    {
        $categories = PropertyCategory::where('active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'slug', 'default_check_in_radius_meters']);

        return response()->json(['data' => $categories]);
    }

    public function tags(): JsonResponse
    {
        $tags = PropertyTag::where('active', true)->orderBy('sort_order')->get(['id', 'name', 'slug', 'color']);

        return response()->json(['data' => $tags]);
    }
}
