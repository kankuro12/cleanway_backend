<?php

namespace App\Http\Controllers\Web;

use App\Domain\Incidents\RaiseIncident;
use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(Request $request): View
    {
        $incidents = Incident::with(['task:id,title', 'property:id,name', 'reporter:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('pages.incidents', ['incidents' => $incidents]);
    }

    public function create(Request $request): View
    {
        $taskId = $request->integer('task_id');

        return view('pages.incident-create', [
            'task' => $taskId ? Task::find($taskId) : null,
        ]);
    }

    public function store(Request $request, RaiseIncident $raiser): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('8.2') || $request->user()->hasPermission('4.4'), 403);

        $request->validate([
            'task_id' => ['nullable', 'exists:tasks,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
            'category' => ['required', 'in:'.implode(',', Incident::CATEGORIES)],
            'severity' => ['required', 'in:'.implode(',', Incident::SEVERITIES)],
            'description' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'evidence.*' => ['nullable', 'image', 'max:10240'],
        ]);

        $incident = $raiser->execute($request->user(), $request->validated(), $request->file('evidence', []));

        return redirect()->route('incidents')->with('status', 'Incident #'.$incident->id.' raised.');
    }

    public function transition(Request $request, Incident $incident, RaiseIncident $raiser): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('8.2'), 403);

        $request->validate([
            'status' => ['required', 'in:'.implode(',', Incident::STATUSES)],
            'resolution' => ['nullable', 'string'],
        ]);

        $raiser->transition($incident, $request->string('status'), $request->user(), ['resolution' => $request->string('resolution')]);

        return back()->with('status', 'Incident moved to '.$request->string('status').'.');
    }
}
