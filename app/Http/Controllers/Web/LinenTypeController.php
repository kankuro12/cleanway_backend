<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLinenTypeRequest;
use App\Models\LinenType;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LinenTypeController extends Controller
{
    public function index(Request $request): View
    {
        $linenTypes = LinenType::query()
            ->withCount('propertyLinens')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.linen-types', [
            'linenTypes' => $linenTypes,
        ]);
    }

    public function options(): JsonResponse
    {
        $types = LinenType::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'rate', 'description']);

        return response()->json([
            'results' => $types,
        ]);
    }

    public function store(StoreLinenTypeRequest $request, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);

        $linenType = DB::transaction(fn () => LinenType::create($data));

        $audit->log('linen_type.created', 'linen_type', $linenType->id, ['after' => ['name' => $linenType->name]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Linen type created successfully.',
                'linen_type' => $linenType,
            ]);
        }

        return redirect()->route('linen-types')->with('status', 'Linen type created successfully.');
    }

    public function update(StoreLinenTypeRequest $request, LinenType $linenType, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);

        DB::transaction(fn () => $linenType->update($data));

        $audit->log('linen_type.updated', 'linen_type', $linenType->id, ['after' => ['name' => $linenType->name]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Linen type updated successfully.',
                'linen_type' => $linenType,
            ]);
        }

        return redirect()->route('linen-types')->with('status', 'Linen type updated successfully.');
    }

    public function destroy(LinenType $linenType, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(fn () => $linenType->delete());

        $audit->log('linen_type.deleted', 'linen_type', $linenType->id, ['before' => ['name' => $linenType->name]]);

        return redirect()->route('linen-types')->with('status', 'Linen type deleted successfully.');
    }
}
