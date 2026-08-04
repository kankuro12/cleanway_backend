<?php

namespace App\Http\Controllers\Web;

use App\Domain\Tasks\AssignTask;
use App\Domain\Tasks\CreateTask;
use App\Domain\Tasks\RescheduleTask;
use App\Domain\Tasks\TransitionTaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\ChecklistTemplate;
use App\Models\Property;
use App\Models\Task;
use App\Models\TaskAssignment;
use App\Models\TaskType;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $tasks = Task::query()
            ->with(['taskType:id,name', 'property:id,name', 'assignments'])
            ->filter($request->only(['status', 'priority', 'task_type_id', 'property_id', 'assignee_id', 'from', 'to']))
            ->orderByDesc('scheduled_start_at')
            ->paginate(25)
            ->withQueryString();

        return view('pages.tasks', [
            'tasks' => $tasks,
            'taskTypes' => TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'properties' => Property::orderBy('name')->get(['id', 'name']),
            'assignees' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return $this->formData('pages.task-create');
    }

    public function store(StoreTaskRequest $request, CreateTask $createTask): RedirectResponse
    {
        try {
            $result = $createTask->execute($request->validated(), $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('tasks.edit', $result['task'])
            ->with('status', 'Task created. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''));
    }

    public function edit(Task $task): View
    {
        $task->load(['taskType:id,name', 'property:id,name', 'assignments.assignee', 'history.user', 'checklistSnapshot', 'evidence', 'subtasks']);

        return $this->formData('pages.task-edit', ['task' => $task]);
    }

    public function update(UpdateTaskRequest $request, Task $task, RescheduleTask $reschedule): RedirectResponse
    {
        try {
            $result = $reschedule->execute(
                $task,
                \Carbon\Carbon::parse($request->string('scheduled_start_at')),
                $request->filled('scheduled_end_at') ? \Carbon\Carbon::parse($request->string('scheduled_end_at')) : null,
                $request->user()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()])->withInput();
        }

        $task->update($request->safe()->except(['scheduled_start_at', 'scheduled_end_at', 'subtasks']));

        // Subtask list replaces the current one (completed rows are kept as-is).
        if ($request->has('subtasks')) {
            $keepIds = collect($request->input('subtasks'))->pluck('id')->filter()->all();
            $task->subtasks()->whereNotIn('id', $keepIds)->whereNull('completed_at')->delete();

            foreach ($request->input('subtasks') as $index => $subtask) {
                if (! empty($subtask['id'])) {
                    continue;
                }
                \App\Models\TaskSubtask::create([
                    'task_id' => $task->id,
                    'title' => $subtask['title'],
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('tasks.edit', $task)->with('status', 'Task updated. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''));
    }

    public function toggleSubtask(Request $request, Task $task, \App\Models\TaskSubtask $subtask): RedirectResponse
    {
        abort_unless($subtask->task_id === $task->id, 404);

        $subtask->update([
            'completed_at' => $subtask->completed_at ? null : now(),
            'completed_by' => $subtask->completed_at ? null : $request->user()->id,
        ]);

        return back()->with('status', $subtask->completed_at ? 'Subtask completed.' : 'Subtask reopened.');
    }

    public function storeSubtask(Request $request, Task $task): RedirectResponse
    {
        $request->validate(['title' => ['required', 'string', 'max:255']]);

        \App\Models\TaskSubtask::create([
            'task_id' => $task->id,
            'title' => $request->string('title'),
            'sort_order' => $task->subtasks()->count(),
        ]);

        return back()->with('status', 'Sub task added.');
    }

    public function transition(Request $request, Task $task, TransitionTaskStatus $transitioner): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:'.implode(',', Task::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $transitioner->transition($task, (string) $request->string('status'), $request->user(), [
                'remarks' => $request->string('remarks'),
                'latitude' => $request->float('latitude'),
                'longitude' => $request->float('longitude'),
            ]);
        } catch (\InvalidArgumentException|\DomainException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', "Task moved to {$request->string('status')}.");
    }

    public function assign(Request $request, Task $task, AssignTask $assigner): RedirectResponse
    {
        $request->validate([
            'assignee_type' => ['required', 'in:user,team'],
            'assignee_id' => ['required', 'integer'],
            'override_warnings' => ['sometimes', 'boolean'],
            'override_reason' => ['nullable', 'required_if:override_warnings,1', 'string', 'max:500'],
        ]);

        try {
            $result = $assigner->execute(
                $task,
                $request->string('assignee_type'),
                $request->integer('assignee_id'),
                $request->user(),
                (bool) $request->boolean('override_warnings'),
                $request->string('override_reason'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        return back()->with('status', 'Assignee added. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''));
    }

    public function unassign(Request $request, Task $task, TaskAssignment $assignment, AssignTask $assigner): RedirectResponse
    {
        $assigner->remove($task, $assignment, $request->user());

        return back()->with('status', 'Assignment removed.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('4.6'), 403);

        $task->delete();

        return redirect()->route('tasks')->with('status', 'Task deleted.');
    }

    public function uploadEvidence(Request $request, Task $task, \App\Domain\Tasks\UploadTaskEvidence $uploader): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('4.4'), 403);

        $request->validate([
            'evidence' => ['required', 'image', 'max:10240'],
            'evidence_type' => ['required', 'in:before,during,after,issue,safety,access_problem,other'],
            'captured_at' => ['nullable', 'date'],
        ]);

        $evidence = $uploader->execute(
            $task,
            $request->user(),
            $request->file('evidence'),
            $request->string('evidence_type'),
            ['captured_at' => $request->date('captured_at'), 'source' => 'web'],
        );

        return back()->with('status', 'Evidence '.$evidence->id.' uploaded ('.$evidence->evidence_type.').');
    }

    private function formData(string $view, array $extra = []): View
    {
        return view($view, array_merge([
            'taskTypes' => TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name', 'default_priority', 'default_estimated_duration_minutes', 'approval_required']),
            'properties' => Property::where('active', true)->orderBy('name')->get(['id', 'name', 'address']),
            'checklists' => ChecklistTemplate::where('active', true)->orderBy('name')->get(['id', 'name']),
            'managers' => User::where('role', User::ROLE_SUPERVISOR)->orderBy('name')->get(['id', 'name']),
            'cleaners' => User::where('role', User::ROLE_CLEANER)->orderBy('name')->get(['id', 'name']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
        ], $extra));
    }
}
