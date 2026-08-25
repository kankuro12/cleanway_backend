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
        $term = $request->string('q')->trim()->toString();

        $properties = Property::where('active', true)
            ->with('client:id,name,company_name')
            ->when($term !== '', function ($q) use ($term): void {
                $q->where(function ($q) use ($term): void {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('property_code', 'like', "%{$term}%")
                        ->orWhere('address', 'like', "%{$term}%")
                        ->orWhere('formatted_address', 'like', "%{$term}%")
                        ->orWhereHas('client', function ($cq) use ($term): void {
                            $cq->where('name', 'like', "%{$term}%")
                               ->orWhere('company_name', 'like', "%{$term}%");
                        });
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'property_code', 'address', 'formatted_address', 'latitude', 'longitude', 'client_id']);

        return response()->json([
            'results' => $properties->map(fn ($property) => [
                'id' => $property->id,
                'text' => $property->dropdown_label,
                'name' => $property->name,
                'property_code' => $property->property_code,
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

    public function massManage(Request $request): View
    {
        $properties = Property::query()
            ->with(['client:id,name,company_name', 'beds', 'linens'])
            ->orderBy('name')
            ->get();

        return view('pages.properties-mass-manage', [
            'properties' => $properties,
            'clients' => Client::where('active', true)->orderBy('name')->get(['id', 'name', 'company_name']),
            'categories' => PropertyCategory::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'bedTypes' => BedType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'linenTypes' => LinenType::where('active', true)->orderBy('sort_order')->get(['id', 'name', 'rate']),
        ]);
    }

    public function massSave(Request $request, AuditLogger $audit, PropertyDetails $details): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'properties' => ['required', 'array'],
            'properties.*.id' => ['nullable', 'integer'],
            'properties.*.client_id' => ['nullable', 'exists:clients,id'],
            'properties.*.name' => ['required', 'string', 'max:255'],
            'properties.*.address' => ['required', 'string', 'max:500'],
            'properties.*.bedrooms_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'properties.*.bathrooms_count' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'properties.*.parking_type' => ['nullable', 'string', 'max:50'],
            'properties.*.parking_spaces_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'properties.*.active' => ['nullable', 'boolean'],
            'properties.*._delete' => ['nullable', 'boolean'],
            'properties.*.beds' => ['nullable', 'array'],
            'properties.*.linens' => ['nullable', 'array'],
        ]);

        $savedCount = 0;

        DB::transaction(function () use ($data, $request, $details, $audit, &$savedCount): void {
            foreach ($data['properties'] as $row) {
                if (!empty($row['_delete']) && !empty($row['id'])) {
                    $prop = Property::find($row['id']);
                    if ($prop) {
                        $prop->update(['active' => false]);
                        $prop->delete();
                        $audit->log('property.archived', 'property', $prop->id);
                    }
                    continue;
                }

                $propData = [
                    'client_id' => !empty($row['client_id']) ? (int) $row['client_id'] : null,
                    'name' => trim($row['name']),
                    'address' => trim($row['address']),
                    'bedrooms_count' => (int) ($row['bedrooms_count'] ?? 0),
                    'bathrooms_count' => (float) ($row['bathrooms_count'] ?? 1.0),
                    'parking_type' => (string) ($row['parking_type'] ?? 'none'),
                    'parking_spaces_count' => (int) ($row['parking_spaces_count'] ?? 0),
                    'active' => isset($row['active']) ? (bool) $row['active'] : true,
                ];

                if (!empty($row['id'])) {
                    $property = Property::find($row['id']);
                    if ($property) {
                        $property->update($propData + ['updated_by' => $request->user()?->id]);
                    }
                } else {
                    $property = Property::create($propData + [
                        'created_by' => $request->user()?->id,
                        'updated_by' => $request->user()?->id,
                    ]);
                }

                if ($property) {
                    $details->save($property, [
                        'beds' => $row['beds'] ?? [],
                        'linens' => $row['linens'] ?? [],
                    ]);
                    $savedCount++;
                }
            }
        });

        $audit->log('properties.mass_saved', 'property', 0, ['count' => $savedCount]);

        return response()->json([
            'success' => true,
            'message' => "Successfully saved {$savedCount} " . ($savedCount === 1 ? 'property' : 'properties') . '.',
        ]);
    }
}
