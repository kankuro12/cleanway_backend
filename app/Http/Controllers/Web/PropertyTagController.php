<?php

namespace App\Http\Controllers\Web;

use App\Domain\Properties\MergePropertyTags;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyTagRequest;
use App\Models\Property;
use App\Models\PropertyTag;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PropertyTagController extends Controller
{
    public function index(): View
    {
        return view('pages.property-tags', [
            'tags' => PropertyTag::withCount('properties')->orderBy('sort_order')->paginate(50),
        ]);
    }

    public function store(StorePropertyTagRequest $request, AuditLogger $audit): RedirectResponse
    {
        $tag = DB::transaction(function () use ($request): PropertyTag {
            return PropertyTag::create($request->validated() + ['slug' => PropertyTag::uniqueSlug($request->string('name'))]);
        });

        $audit->log('property_tag.created', 'property_tag', $tag->id, ['after' => ['name' => $tag->name]]);

        return redirect()->route('property-tags')->with('status', 'Tag created.');
    }

    public function update(StorePropertyTagRequest $request, PropertyTag $tag, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($request, $tag): void {
            $tag->update($request->validated());
        });

        $audit->log('property_tag.updated', 'property_tag', $tag->id);

        return redirect()->route('property-tags')->with('status', 'Tag updated.');
    }

    public function bulkAssign(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('3.5'), 403);

        $request->validate([
            'property_ids' => ['required', 'array', 'min:1'],
            'property_ids.*' => ['integer', 'exists:properties,id'],
            'tag_ids' => ['required', 'array', 'min:1'],
            'tag_ids.*' => ['integer', 'exists:property_tags,id'],
        ]);

        $properties = Property::whereIn('id', $request->input('property_ids'))->get();

        DB::transaction(function () use ($properties, $request): void {
            foreach ($properties as $property) {
                $property->tags()->syncWithoutDetaching($request->input('tag_ids'));
            }
        });

        return redirect()->route('properties')->with('status', 'Tags assigned to '.count($properties).' properties.');
    }

    public function bulkRemove(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('3.5'), 403);

        $request->validate([
            'property_ids' => ['required', 'array', 'min:1'],
            'property_ids.*' => ['integer', 'exists:properties,id'],
            'tag_ids' => ['required', 'array', 'min:1'],
            'tag_ids.*' => ['integer', 'exists:property_tags,id'],
        ]);

        $properties = Property::whereIn('id', $request->input('property_ids'))->get();

        DB::transaction(function () use ($properties, $request): void {
            foreach ($properties as $property) {
                $property->tags()->detach($request->input('tag_ids'));
            }
        });

        return redirect()->route('properties')->with('status', 'Tags removed from '.count($properties).' properties.');
    }

    public function merge(Request $request, MergePropertyTags $merger): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('3.5'), 403);

        $request->validate([
            'keep_tag_id' => ['required', 'integer', 'exists:property_tags,id'],
            'merge_tag_id' => ['required', 'integer', 'exists:property_tags,id', 'different:keep_tag_id'],
        ]);

        $keep = PropertyTag::findOrFail($request->integer('keep_tag_id'));
        $merge = PropertyTag::findOrFail($request->integer('merge_tag_id'));

        $merger->execute($keep, $merge);

        return redirect()->route('property-tags')->with('status', 'Tag merged.');
    }
}
