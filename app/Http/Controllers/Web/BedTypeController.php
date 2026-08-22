<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBedTypeRequest;
use App\Models\BedType;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BedTypeController extends Controller
{
    public function index(Request $request): View
    {
        $bedTypes = BedType::query()
            ->withCount('propertyBeds')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.bed-types', [
            'bedTypes' => $bedTypes,
        ]);
    }

    public function options(): JsonResponse
    {
        $types = BedType::where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return response()->json([
            'results' => $types,
        ]);
    }

    public function store(StoreBedTypeRequest $request, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);

        $bedType = DB::transaction(fn () => BedType::create($data));

        $audit->log('bed_type.created', 'bed_type', $bedType->id, ['after' => ['name' => $bedType->name]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bed type created successfully.',
                'bed_type' => $bedType,
            ]);
        }

        return redirect()->route('bed-types')->with('status', 'Bed type created successfully.');
    }

    public function update(StoreBedTypeRequest $request, BedType $bedType, AuditLogger $audit): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active', true);

        DB::transaction(fn () => $bedType->update($data));

        $audit->log('bed_type.updated', 'bed_type', $bedType->id, ['after' => ['name' => $bedType->name]]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Bed type updated successfully.',
                'bed_type' => $bedType,
            ]);
        }

        return redirect()->route('bed-types')->with('status', 'Bed type updated successfully.');
    }

    public function destroy(BedType $bedType, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(fn () => $bedType->delete());

        $audit->log('bed_type.deleted', 'bed_type', $bedType->id, ['before' => ['name' => $bedType->name]]);

        return redirect()->route('bed-types')->with('status', 'Bed type deleted successfully.');
    }
}
