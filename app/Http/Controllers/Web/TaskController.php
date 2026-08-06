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
    /**
     * Task list — all users' tasks. Gated by permission:4.9.
     */
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

    /**
     * My tasks — current user's own assignments, two tabs (current / finished).
     */
    public function my(Request $request): View
    {
        // Only terminal states count as finished — a task awaiting approval is
        // still open until a supervisor/manager approves it.
        $finished = [Task::STATUS_COMPLETED, Task::STATUS_APPROVED, Task::STATUS_REJECTED, Task::STATUS_CANCELLED];

        $current = Task::query()
            ->with(['taskType:id,name', 'property:id,name', 'assignments'])
            ->forUser($request->user())
            ->whereNotIn('status', $finished)
            ->orderBy('scheduled_start_at')
            ->paginate(25, ['*'], 'current_page');

        $done = Task::query()
            ->with(['taskType:id,name', 'property:id,name', 'assignments'])
            ->forUser($request->user())
            ->whereIn('status', $finished)
            ->orderBy('scheduled_start_at')
            ->paginate(25, ['*'], 'finished_page');

        return view('pages.tasks-cleaner', [
            'current' => $current,
            'finished' => $done,
            'tab' => $request->string('tab', 'current')->toString(),
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

    public function edit(Task $task): View|RedirectResponse
    {
        // Cleaners (and anyone without edit permission) get the work page instead.
        if (! auth()->user()->hasPermission('4.3')) {
            return redirect()->route('tasks.work', $task);
        }

        $task->load(['taskType:id,name', 'property:id,name', 'assignments.assignee', 'history.user', 'checklistSnapshot', 'evidence', 'subtasks']);

        return $this->formData('pages.task-edit', ['task' => $task]);
    }

    /**
     * Cleaner-facing work page: task detail + punch-in + subtasks + evidence + complete.
     */
    public function work(Task $task): View
    {
        $task->load(['taskType:id,name', 'property:id,name', 'assignments.assignee', 'history.user', 'checklistSnapshot', 'evidence', 'subtasks']);

        $lastPunch = \App\Models\AttendanceEvent::where('task_id', $task->id)
            ->where('user_id', auth()->id())
            ->where('event_type', \App\Models\AttendanceEvent::TYPE_CLOCK_IN)
            ->latest('id')
            ->first();

        $punchData = $lastPunch ? [
            'punched_in_at' => $lastPunch->server_timestamp?->toIso8601String(),
            'latitude' => $lastPunch->latitude,
            'longitude' => $lastPunch->longitude,
            'distance_meters' => $lastPunch->distance_from_property_meters,
            'radius_meters' => $lastPunch->effective_radius_meters,
            'inside_geofence' => $lastPunch->inside_geofence,
            'property_latitude' => $task->latitude_snapshot,
            'property_longitude' => $task->longitude_snapshot,
            'property_name' => $task->property_name_snapshot,
            'reason' => null,
        ] : null;

        return $this->formData('pages.task-work', ['task' => $task, 'lastPunch' => $punchData]);
    }

    public function workCheckIn(Request $request, Task $task, \App\Domain\Tasks\CheckInToTask $checkIn): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'gps_accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:10000'],
        ]);

        try {
            $result = $checkIn->execute($task, $request->user(), $request->all() + ['source' => 'web']);

            // Work starts only on a successful (inside-geofence) punch-in.
            if (! $result['blocked']
                && $result['inside_geofence'] === true
                && in_array($task->fresh()->status, [Task::STATUS_ASSIGNED, Task::STATUS_ACCEPTED], true)) {
                app(\App\Domain\Tasks\TransitionTaskStatus::class)->transition($task->fresh(), Task::STATUS_IN_PROGRESS, $request->user(), ['source' => 'web']);
            }
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $event = $result['event'];

        $punch = [
            'id' => $event->id,
            'punched_in_at' => $event->server_timestamp?->toIso8601String(),
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'gps_accuracy_meters' => $event->gps_accuracy_meters,
            'distance_meters' => $event->distance_from_property_meters,
            'radius_meters' => $event->effective_radius_meters,
            'inside_geofence' => $event->inside_geofence,
            'property_latitude' => $task->latitude_snapshot,
            'property_longitude' => $task->longitude_snapshot,
            'property_name' => $task->property_name_snapshot,
            'policy' => $result['exception']?->policy,
            'reason' => $result['exception']?->reason,
        ];

        return response()->json([
            'message' => $result['blocked'] ? 'Outside the permitted check-in radius — punch-in recorded, supervisor approval required.' : 'Work started.',
            'inside_geofence' => $result['inside_geofence'],
            'blocked' => $result['blocked'],
            'task_status' => $task->fresh()->status,
            'punch' => $punch,
        ], $result['blocked'] ? 403 : 200);
    }

    public function completeTask(Request $request, Task $task, \App\Domain\Tasks\CompleteTask $completer): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'responses' => ['sometimes', 'array'],
            'responses.*.snapshot_item_id' => ['required', 'integer'],
            'responses.*.value' => ['required', 'string', 'max:5000'],
            'remarks' => ['sometimes', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $result = $completer->execute(
            $task,
            $request->user(),
            $request->input('responses', []),
            (string) $request->string('remarks'),
            $request->only(['latitude', 'longitude']) + ['source' => 'web'],
        );

        if (! $result['ok']) {
            return response()->json(['message' => 'Task cannot be completed.', 'missing' => $result['missing']], 422);
        }

        return response()->json([
            'message' => $task->fresh()->approval_required ? 'Completed and submitted for approval.' : 'Task completed.',
            'task_status' => $task->fresh()->status,
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task, RescheduleTask $reschedule): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $result = ['warnings' => []];
        if ($request->filled('scheduled_start_at')) {
            try {
                $result = $reschedule->execute(
                    $task,
                    \Illuminate\Support\Carbon::parse($request->string('scheduled_start_at')),
                    $request->filled('scheduled_end_at') ? \Illuminate\Support\Carbon::parse($request->string('scheduled_end_at')) : null,
                    $request->user()
                );
            } catch (\InvalidArgumentException $e) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => $e->getMessage()], 422);
                }
                return back()->withErrors(['task' => $e->getMessage()])->withInput();
            }
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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Task updated. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''),
                'task' => $task->fresh(),
            ]);
        }

        return redirect()->route('tasks.edit', $task)->with('status', 'Task updated. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''));
    }

    public function toggleSubtask(Request $request, Task $task, \App\Models\TaskSubtask $subtask): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($subtask->task_id === $task->id, 404);

        $subtask->update([
            'completed_at' => $subtask->completed_at ? null : now(),
            'completed_by' => $subtask->completed_at ? null : $request->user()->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $subtask->id,
                'title' => $subtask->title,
                'completed' => $subtask->completed_at !== null,
            ]);
        }

        return back()->with('status', $subtask->completed_at ? 'Subtask completed.' : 'Subtask reopened.');
    }

    public function storeSubtask(Request $request, Task $task): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->validate(['title' => ['required', 'string', 'max:255']]);

        $subtask = \App\Models\TaskSubtask::create([
            'task_id' => $task->id,
            'title' => $request->string('title'),
            'sort_order' => $task->subtasks()->count(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subtask added.',
                'subtask' => $subtask,
            ], 201);
        }

        return back()->with('status', 'Sub task added.');
    }

    public function transition(Request $request, Task $task, TransitionTaskStatus $transitioner): RedirectResponse|\Illuminate\Http\JsonResponse
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
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        $fresh = $task->fresh();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Task moved to {$request->string('status')}.",
                'status' => $fresh->status,
                'formatted_status' => ucfirst(str_replace('_', ' ', $fresh->status)),
                'transitionable_statuses' => array_values($fresh->transitionableStatuses()),
                'history_entry' => [
                    'previous_status' => $task->history()->latest('id')->first()?->previous_status,
                    'new_status' => $fresh->status,
                    'user_name' => $request->user()->name,
                    'created_at' => now()->format('j M H:i'),
                    'remarks' => $request->string('remarks') ?: '—',
                ],
            ]);
        }

        return back()->with('status', "Task moved to {$request->string('status')}.");
    }

    public function assign(Request $request, Task $task, AssignTask $assigner): RedirectResponse|\Illuminate\Http\JsonResponse
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
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['task' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            $assignment = $result['assignment'] ?? $task->assignments()->latest('id')->first();
            $assignment->load('assignee');
            return response()->json([
                'message' => 'Assignee added. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''),
                'assignment' => [
                    'id' => $assignment->id,
                    'assignee_name' => $assignment->assignee?->name ?? ('#'.$assignment->assignee_id),
                    'assignee_type' => $assignment->assignee_type,
                    'status' => $assignment->status,
                    'delete_url' => route('tasks.unassign', [$task, $assignment]),
                ],
            ]);
        }

        return back()->with('status', 'Assignee added. '.($result['warnings'] ? implode(' ', $result['warnings']) : ''));
    }

    public function unassign(Request $request, Task $task, TaskAssignment $assignment, AssignTask $assigner): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $assigner->remove($task, $assignment, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Assignment removed.',
                'assignment_id' => $assignment->id,
            ]);
        }

        return back()->with('status', 'Assignment removed.');
    }

    public function destroy(Request $request, Task $task): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('4.6'), 403);

        $task->delete();

        return redirect()->route('tasks')->with('status', 'Task deleted.');
    }

    public function uploadEvidence(Request $request, Task $task, \App\Domain\Tasks\UploadTaskEvidence $uploader): RedirectResponse|\Illuminate\Http\JsonResponse
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

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $evidence->id,
                'evidence_type' => $evidence->evidence_type,
                'original_filename' => $evidence->original_filename,
                'size_bytes' => $evidence->size_bytes,
                'processing_status' => $evidence->processing_status,
                'view_url' => route('evidence.view', $evidence),
            ], 201);
        }

        return back()->with('status', 'Evidence '.$evidence->id.' uploaded ('.$evidence->evidence_type.').');
    }

    public function deleteEvidence(Request $request, Task $task, \App\Models\TaskEvidence $evidence): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        abort_unless($evidence->task_id === $task->id, 404);
        abort_unless(in_array($request->user()->role, [User::ROLE_ADMIN, User::ROLE_SUPERVISOR], true), 403);

        $evidence->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Evidence photo deleted.',
                'evidence_id' => $evidence->id,
            ]);
        }

        return back()->with('status', 'Evidence photo deleted.');
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
            'people' => User::orderBy('name')->get(['id', 'name', 'role']),
            'categories' => \App\Models\PropertyCategory::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'checklistEnabled' => (bool) app(\App\Services\Settings\SettingsService::class)->get('pref_ui_checklist_enabled_'.auth()->id(), '0', \App\Models\Setting::SCOPE_SYSTEM),
        ], $extra));
    }
}
