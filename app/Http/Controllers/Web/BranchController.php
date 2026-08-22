<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('pages.branches', [
            'branches' => Branch::withCount('users')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
        ]);

        DB::transaction(fn () => Branch::create($data));

        return redirect()->route('branches')->with('status', 'Branch office created with geofence location.');
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:10000'],
            'active' => ['sometimes', 'boolean'],
        ]);

        DB::transaction(fn () => $branch->update($data));

        return redirect()->route('branches')->with('status', 'Branch office updated.');
    }
}
