<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskTypeRequest;
use App\Models\ChecklistTemplate;
use App\Models\TaskType;
use App\Services\Audit\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TaskTypeController extends Controller
{
    public function index(): View
    {
        return view('pages.task-types', [
            'taskTypes' => TaskType::withCount('tasks')->orderBy('sort_order')->paginate(50),
            'checklists' => ChecklistTemplate::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreTaskTypeRequest $request, AuditLogger $audit): RedirectResponse
    {
        $taskType = DB::transaction(function () use ($request): TaskType {
            return TaskType::create($request->validated() + ['slug' => TaskType::uniqueSlug($request->string('name'))]);
        });

        $audit->log('task_type.created', 'task_type', $taskType->id, ['after' => ['name' => $taskType->name]]);

        return redirect()->route('task-types')->with('status', 'Task type created.');
    }

    public function update(StoreTaskTypeRequest $request, TaskType $taskType, AuditLogger $audit): RedirectResponse
    {
        DB::transaction(function () use ($request, $taskType): void {
            $taskType->update($request->validated());
        });

        $audit->log('task_type.updated', 'task_type', $taskType->id);

        return redirect()->route('task-types')->with('status', 'Task type updated.');
    }
}
