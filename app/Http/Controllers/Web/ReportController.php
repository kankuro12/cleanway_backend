<?php

namespace App\Http\Controllers\Web;

use App\Domain\Reports\ReportService;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateExport;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $type = $request->string('type', 'tasks')->toString();
        $filters = $request->only(['from', 'to', 'status', 'priority', 'task_type_id', 'property_id', 'assignee_id', 'user_id', 'geocode_status', 'missing_coords', 'unassigned', 'action', 'category']);

        $report = in_array($type, ['attendance', 'tasks', 'approvals', 'properties', 'incidents'], true)
            ? $reports->run($type, $filters)
            : ['headers' => [], 'rows' => []];

        return view('pages.reports', [
            'type' => $type,
            'report' => $report,
            'exports' => ExportJob::where('requested_by', $request->user()->id)->orderByDesc('id')->limit(10)->get(),
            'workers' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
            'taskTypes' => \App\Models\TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'properties' => \App\Models\Property::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('7.2'), 403);

        $request->validate(['type' => ['required', 'in:attendance,tasks,approvals,properties,incidents']]);

        $job = ExportJob::create([
            'type' => $request->string('type'),
            'filters' => $request->only(['from', 'to', 'status', 'priority', 'task_type_id', 'property_id', 'assignee_id', 'user_id', 'geocode_status', 'missing_coords', 'unassigned', 'action', 'category']),
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        GenerateExport::dispatch($job->id);

        return redirect()->route('reports', ['type' => $job->type])->with('status', 'Export queued — refresh to see progress.');
    }

    public function download(Request $request, ExportJob $job): StreamedResponse
    {
        abort_unless($request->user()->hasPermission('7.2') && $job->requested_by === $request->user()->id, 403);

        abort_unless($job->status === ExportJob::STATUS_DONE && $job->file_path, 404);

        return Storage::disk('evidence')->download($job->file_path, basename($job->file_path));
    }
}
