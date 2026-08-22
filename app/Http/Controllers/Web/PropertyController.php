<?php

namespace App\Http\Controllers\Web;

use App\Domain\Properties\CreateProperty;
use App\Domain\Properties\RetryPropertyGeocode;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\BedType;
use App\Models\Client;
use App\Models\LinenType;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyTag;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Support\PropertyDetails;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $properties = Property::query()
            ->with(['category:id,name', 'tags:id,name,color', 'client:id,name,company_name'])
            ->filter($request->only(['search', 'active', 'category_id', 'client_id', 'geocode_status', 'missing_coords', 'unassigned', 'tag_id', 'assigned_to']))
            ->orderByDesc('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('pages.properties', [
            'properties' => $properties,
            'clients' => Client::where('active', true)->orderBy('name')->get(['id', 'name', 'company_name']),
        ]);
    }

    public function create(): View
    {
        return view('pages.property-create', [
            'categories' => PropertyCategory::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'tags' => PropertyTag::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'clients' => Client::where('active', true)->orderBy('name')->get(['id', 'name', 'company_name']),
            'bedTypes' => BedType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'linenTypes' => LinenType::where('active', true)->orderBy('sort_order')->get(['id', 'name', 'rate']),
        ]);
    }

    /**
     * Select2-friendly property options (id/text + location data for autofill).
     */
    public function options(Request $request): \Illuminate\Http\JsonResponse
    {
        $term = $request->string('q');

        $properties = Property::where('active', true)
            ->when($term !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('address', 'like', "%{$term}%")
                ->orWhere('formatted_address', 'like', "%{$term}%")))
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'address', 'formatted_address', 'latitude', 'longitude']);

        return response()->json([
            'results' => $properties->map(fn ($property) => [
                'id' => $property->id,
                'text' => $property->name.' — '.($property->formatted_address ?: $property->address),
                'address' => $property->formatted_address ?: $property->address,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
            ]),
        ]);
    }

    public function store(StorePropertyRequest $request, CreateProperty $createProperty, PropertyDetails $details): RedirectResponse
    {
        $property = $createProperty->execute($request->safe()->except(['tags', 'beds', 'linens']), $request->user());

        if ($request->filled('tags')) {
            $property->tags()->sync($request->input('tags'));
        }

        $details->save($property, $request->validated());

        return redirect()->route('properties.edit', $property)
            ->with('status', 'Property created. Coordinates will resolve in the background.');
    }

    public function edit(Property $property): View
    {
        $property->load([
            'category', 'tags', 'client',
            'beds.bedType', 'linens.linenType',
            'assignments.assignable',
            'geocodeAttempts' => fn ($q) => $q->latest(),
        ]);

        return view('pages.property-edit', [
            'property' => $property,
            'categories' => PropertyCategory::orderBy('sort_order')->get(['id', 'name']),
            'tags' => PropertyTag::orderBy('sort_order')->get(['id', 'name']),
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company_name']),
            'bedTypes' => BedType::orderBy('sort_order')->get(['id', 'name']),
            'linenTypes' => LinenType::orderBy('sort_order')->get(['id', 'name', 'rate']),
            'managers' => User::where('role', User::ROLE_SUPERVISOR)->orderBy('name')->get(['id', 'name']),
            'people' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property, AuditLogger $audit, PropertyDetails $details): RedirectResponse
    {
        DB::transaction(function () use ($request, $property, $audit): void {
            $before = $property->only(['name', 'address', 'property_category_id', 'client_id', 'active']);

            $property->update($request->safe()->except(['tags', 'beds', 'linens']) + ['updated_by' => $request->user()->id]);

            if ($request->has('tags')) {
                $property->tags()->sync($request->input('tags'));
            }

            $audit->log('property.updated', 'property', $property->id, [
                'before' => $before,
                'after' => $property->only(['name', 'address', 'property_category_id', 'client_id', 'active']),
            ]);
        });

        $details->save($property, $request->validated());

        return redirect()->route('properties.edit', $property)->with('status', 'Property updated.');
    }

    public function destroy(Request $request, Property $property, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($property, $audit): void {
            $property->update(['active' => false]);
            $property->delete();

            $audit->log('property.archived', 'property', $property->id);
        });

        return redirect()->route('properties')->with('status', 'Property archived.');
    }

    public function retryGeocode(Request $request, Property $property, RetryPropertyGeocode $retry): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('3.3'), 403);

        $retry->execute($property, $request->float('latitude'), $request->float('longitude'));

        return redirect()->route('properties.edit', $property)->with('status', 'Geocoding retried.');
    }
}
