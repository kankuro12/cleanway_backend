<?php

namespace App\Http\Controllers\Web;

use App\Domain\Tasks\GenerateRecurringTasks;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecurrenceRequest;
use App\Models\ChecklistTemplate;
use App\Models\Property;
use App\Models\TaskRecurrence;
use App\Models\TaskType;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RecurrenceController extends Controller
{
    public function index(): View
    {
        return view('pages.recurrences', [
            'recurrences' => TaskRecurrence::with(['property:id,name,property_code,address,client_id', 'property.client:id,name,company_name', 'taskType:id,name'])->orderByDesc('id')->paginate(50),
            'taskTypes' => TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'properties' => Property::where('active', true)->with('client:id,name,company_name')->orderBy('name')->get(['id', 'name', 'property_code', 'address', 'client_id']),
            'checklists' => ChecklistTemplate::where('active', true)->orderBy('name')->get(['id', 'name']),
            'assignees' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreRecurrenceRequest $request, AuditLogger $audit): RedirectResponse
    {
        $recurrence = DB::transaction(function () use ($request): TaskRecurrence {
            return TaskRecurrence::create($request->validated() + ['created_by' => $request->user()->id]);
        });

        $audit->log('task_recurrence.created', 'task_recurrence', $recurrence->id, ['after' => ['rule' => $recurrence->rule]]);

        return redirect()->route('recurrences')->with('status', 'Recurrence template created.');
    }

    public function destroy(Request $request, TaskRecurrence $recurrence, AuditLogger $audit): RedirectResponse
    {
        $recurrence->update(['active' => false]);
        $recurrence->delete();

        $audit->log('task_recurrence.deleted', 'task_recurrence', $recurrence->id);

        return redirect()->route('recurrences')->with('status', 'Recurrence template removed.');
    }

    public function generateNow(Request $request, TaskRecurrence $recurrence, GenerateRecurringTasks $generator): RedirectResponse
    {
        $count = $generator->generate($recurrence, 30, $request->user());

        return redirect()->route('recurrences')->with('status', "Generated {$count} task instances.");
    }
}
