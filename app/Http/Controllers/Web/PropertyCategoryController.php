<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyCategoryRequest;
use App\Models\PropertyCategory;
use App\Models\Team;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PropertyCategoryController extends Controller
{
    public function index(): View
    {
        return view('pages.property-categories', [
            'categories' => PropertyCategory::withCount('properties')->orderBy('sort_order')->paginate(50),
            'managers' => User::where('role', User::ROLE_SUPERVISOR)->orderBy('name')->get(['id', 'name']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePropertyCategoryRequest $request, AuditLogger $audit): RedirectResponse
    {
        $category = DB::transaction(function () use ($request): PropertyCategory {
            return PropertyCategory::create($request->validated() + ['slug' => PropertyCategory::uniqueSlug($request->string('name'))]);
        });

        $audit->log('property_category.created', 'property_category', $category->id, ['after' => ['name' => $category->name]]);

        return redirect()->route('property-categories')->with('status', 'Category created.');
    }

    public function update(StorePropertyCategoryRequest $request, PropertyCategory $category, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($request, $category): void {
            $category->update($request->validated());
        });

        $audit->log('property_category.updated', 'property_category', $category->id);

        return redirect()->route('property-categories')->with('status', 'Category updated.');
    }
}
