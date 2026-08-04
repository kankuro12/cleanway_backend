<?php

namespace App\Http\Controllers\Web;

use App\Domain\Properties\AssignPropertyPersonnel;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyAssignmentRequest;
use App\Models\Property;
use App\Models\PropertyAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyAssignmentController extends Controller
{
    public function store(StorePropertyAssignmentRequest $request, Property $property, AssignPropertyPersonnel $assigner): RedirectResponse
    {
        $assigner->execute($property, $request->validated(), $request->user());

        return redirect()->route('properties.edit', $property)->with('status', 'Assignment added.');
    }

    public function destroy(Request $request, PropertyAssignment $assignment, AssignPropertyPersonnel $assigner): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('3.6'), 403);

        $assigner->remove($assignment, $request->user());

        return redirect()->route('properties.edit', $assignment->property_id)->with('status', 'Assignment removed.');
    }
}
