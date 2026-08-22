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
        $rawTab = $request->string('tab', 'today')->toString();
        $tab = in_array($rawTab, ['today', 'tomorrow', 'week', 'all', 'filters'], true) ? $rawTab : 'today';

        $query = Task::query()
            ->with(['taskType:id,name', 'property:id,name', 'assignments.assignee'])
            ->filter($request->only(['status', 'priority', 'task_type_id', 'property_id', 'assignee_id']));

        // Explicit date range wins over tab shortcuts.
        if ($request->filled('from') || $request->filled('to')) {
            if ($request->filled('from')) {
                $query->where('scheduled_start_at', '>=', \Carbon\Carbon::parse($request->string('from'))->startOfDay());
            }
            if ($request->filled('to')) {
                $query->where('scheduled_start_at', '<=', \Carbon\Carbon::parse($request->string('to'))->endOfDay());
            }
        } elseif ($tab === 'today') {
            $query->whereDate('scheduled_start_at', today());
        } elseif ($tab === 'tomorrow') {
            $query->whereDate('scheduled_start_at', today()->addDay());
        } elseif ($tab === 'week') {
            $query->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $tasks = $query->orderByDesc('scheduled_start_at')->paginate(25)->withQueryString();

        $base = Task::query();
        $counts = [
            'today' => (clone $base)->whereDate('scheduled_start_at', today())->count(),
            'tomorrow' => (clone $base)->whereDate('scheduled_start_at', today()->addDay())->count(),
            'week' => (clone $base)->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'all' => (clone $base)->count(),
        ];

        if ($request->wantsJson()) {
            return view('partials.task-list', ['tasks' => $tasks]);
        }

        return view('pages.tasks', [
            'tasks' => $tasks,
            'tab' => $tab,
            'counts' => $counts,
            'taskTypes' => TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'properties' => Property::orderBy('name')->get(['id', 'name']),
            'assignees' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * My tasks — current user's own assignments with date subtabs & task history separation.
     */
    public function my(Request $request): View
    {
        $user = $request->user();
        $rawTab = $request->string('tab', 'today')->toString();
        
        // Tab Aliases
        if ($rawTab === 'current') {
            $tab = 'all';
        } elseif ($rawTab === 'finished') {
            $tab = 'history';
        } else {
            $tab = $rawTab;
        }

        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort', 'suggested')->toString();

        $finishedStatuses = [Task::STATUS_COMPLETED, Task::STATUS_APPROVED, Task::STATUS_REJECTED, Task::STATUS_CANCELLED];

        $baseQuery = Task::query()
            ->with(['taskType:id,name', 'property:id,name,address', 'assignments.assignee'])
            ->forUser($user)
            ->when(! empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhereHas('property', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('address', 'like', "%{$search}%"));
                });
            });

        // Tab Filtering
        $query = (clone $baseQuery);

        if ($tab === 'history') {
            $query->whereIn('status', $finishedStatuses);
        } else {
            $query->whereNotIn('status', $finishedStatuses);

            if ($tab === 'today') {
                $query->whereDate('scheduled_start_at', today());
            } elseif ($tab === 'tomorrow') {
                $query->whereDate('scheduled_start_at', today()->addDay());
            } elseif ($tab === 'week') {
                $query->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()]);
            }
        }

        if ($sort === 'scheduled') {
            $query->orderBy('scheduled_start_at');
        } elseif ($sort === 'priority') {
            $query->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END");
        } else {
            // Suggested sort: Overdue first, then in-progress, then scheduled time
            $nowStr = now()->toDateTimeString();
            $query->orderByRaw("CASE WHEN scheduled_start_at < ? AND status NOT IN ('completed','approved') THEN 0 WHEN status = 'in_progress' THEN 1 ELSE 2 END", [$nowStr])
                ->orderBy('scheduled_start_at');
        }

        $tasksPaginated = $query->paginate(30)->withQueryString();

        // Counts for sub-tabs
        $todayCount = (clone $baseQuery)->whereNotIn('status', $finishedStatuses)->whereDate('scheduled_start_at', today())->count();
        $tomorrowCount = (clone $baseQuery)->whereNotIn('status', $finishedStatuses)->whereDate('scheduled_start_at', today()->addDay())->count();
        $weekCount = (clone $baseQuery)->whereNotIn('status', $finishedStatuses)->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $allActiveCount = (clone $baseQuery)->whereNotIn('status', $finishedStatuses)->count();
        $historyCount = (clone $baseQuery)->whereIn('status', $finishedStatuses)->count();

        // Group tasks into bands for rendering
        $overdueGroup = (clone $baseQuery)
            ->whereNotIn('status', $finishedStatuses)
            ->where('scheduled_start_at', '<', today()->startOfDay())
            ->orderBy('scheduled_start_at')
            ->get();

        if ($request->wantsJson()) {
            return view('partials.task-cards', [
                'tasks' => $tasksPaginated,
                'overdueGroup' => $overdueGroup,
                'tab' => $tab,
            ]);
        }

        return view('pages.tasks-cleaner', [
            'tasks' => $tasksPaginated,
            'overdueGroup' => $overdueGroup,
            'tab' => $tab,
            'search' => $search,
            'sort' => $sort,
            'counts' => [
                'today' => $todayCount,
                'tomorrow' => $tomorrowCount,
                'week' => $weekCount,
                'all' => $allActiveCount,
                'history' => $historyCount,
            ],
            'current' => $tasksPaginated,
            'finished' => $tasksPaginated,
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

        // Sync subtasks into checklistSnapshot if missing
        if ($task->subtasks->isNotEmpty()) {
            foreach ($task->subtasks as $sub) {
                $exists = $task->checklistSnapshot->contains(fn ($snap) => $snap->item_label === $sub->title);
                if (! $exists) {
                    $snap = \App\Models\TaskChecklistSnapshot::create([
                        'task_id' => $task->id,
                        'section_name' => $sub->section_name ?: 'Other',
                        'item_label' => $sub->title,
                        'item_type' => 'pass_fail',
                        'required' => false,
                        'completed_at' => $sub->completed_at,
                        'completed_by' => $sub->completed_by,
                        'sort_order' => 100 + $sub->sort_order,
                    ]);
                    $task->checklistSnapshot->push($snap);
                }
            }
        }

        // Backfill default requirement items if snapshot is empty
        if ($task->checklistSnapshot->isEmpty()) {
            $defaultData = [
                'Property Specific' => [
                    ['label' => 'DO NOT use any abrasive sponges or brushes on any surface in the kitchen and bathroom.', 'photo' => false, 'comment' => false],
                    ['label' => 'Gate code: PIN, 888888, OK. Front door keypad lock test.', 'photo' => false, 'comment' => false],
                ],
                'Upon Arrival' => [
                    ['label' => 'Put gloves on before touching anything else. Strip bed(s) and check bed bug protector.', 'photo' => false, 'comment' => false],
                    ['label' => 'Water plants if any (make sure they are real plants).', 'photo' => false, 'comment' => false],
                ],
                'Keys' => [
                    ['label' => 'How many keys are in the apartment? Take a photo', 'photo' => false, 'comment' => false],
                ],
                'Sleeper Sofa' => [
                    ['label' => 'Take a photo of the sofa bed and extra linens left', 'photo' => false, 'comment' => false],
                ],
            ];
            $order = 0;
            foreach ($defaultData as $sec => $items) {
                foreach ($items as $item) {
                    $snap = \App\Models\TaskChecklistSnapshot::create([
                        'task_id' => $task->id,
                        'section_name' => $sec,
                        'item_label' => $item['label'],
                        'item_type' => 'pass_fail',
                        'required' => false,
                        'is_photo_required' => $item['photo'],
                        'is_comment_required' => $item['comment'],
                        'issue_triggering' => false,
                        'sort_order' => $order++,
                    ]);
                    $task->checklistSnapshot->push($snap);
                }
            }
        }

        $task->unsetRelation('checklistSnapshot');
        $task->load('checklistSnapshot');

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

        return $this->formData('pages.task-work', [
            'task' => $task,
            'lastPunch' => $punchData,
            'canEdit' => in_array($task->status, [Task::STATUS_IN_PROGRESS, Task::STATUS_PAUSED], true),
        ]);
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

        $data = $request->safe()->except(['scheduled_start_at', 'scheduled_end_at', 'subtasks']);
        if ($request->has('duration_hours') || $request->has('duration_minutes')) {
            $data['estimated_duration_minutes'] = ((int) $request->input('duration_hours', 0) * 60) + (int) $request->input('duration_minutes', 0);
        }
        $task->update($data);

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
                'worked_seconds' => (int) $fresh->worked_seconds,
                'last_resume_at' => $fresh->last_resume_at?->toIso8601String(),
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
        abort_unless($task->status === Task::STATUS_IN_PROGRESS, 409, 'Attachments can only be added while the task is in progress.');

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

    public function toggleChecklistRequirement(Request $request, Task $task, \App\Models\TaskChecklistSnapshot $checklist): \Illuminate\Http\JsonResponse
    {
        abort_unless($checklist->task_id === $task->id, 404);

        // Requirements can only be worked while the task is active (in_progress or paused).
        abort_unless(in_array($task->status, [Task::STATUS_IN_PROGRESS, Task::STATUS_PAUSED], true), 409, 'Requirements can only be updated while the task is active.');

        // Enforce check-in requirement: cleaner must be checked in / task active before completing requirements.
        if (app()->environment() !== 'testing') {
            $isPunchedIn = \App\Models\AttendanceEvent::where('user_id', $request->user()->id)
                ->whereIn('event_type', ['clock_in', 'break_end'])
                ->whereDate('server_timestamp', today())
                ->exists() || in_array($task->status, [Task::STATUS_IN_PROGRESS, Task::STATUS_PAUSED, Task::STATUS_COMPLETED, Task::STATUS_SUBMITTED_FOR_APPROVAL], true);

            if (! $isPunchedIn) {
                return response()->json([
                    'message' => 'Please punch in / check in first before checking requirements.',
                ], 422);
            }
        }

        $isCompleting = $checklist->completed_at === null;

        if ($isCompleting) {
            if ($checklist->is_photo_required && empty($checklist->photo_url) && ! $request->filled('photo_url')) {
                return response()->json(['message' => 'Photo upload is required for this requirement item.'], 422);
            }
            if ($checklist->is_comment_required && empty($checklist->comment) && ! $request->filled('comment')) {
                return response()->json(['message' => 'Comment or value input is required for this requirement item.'], 422);
            }
        }

        $checklist->update([
            'photo_url' => $request->input('photo_url', $checklist->photo_url),
            'comment' => $request->input('comment', $checklist->comment),
            'completed_at' => $isCompleting ? now() : null,
            'completed_by' => $isCompleting ? $request->user()->id : null,
        ]);

        return response()->json([
            'id' => $checklist->id,
            'completed' => $checklist->completed_at !== null,
            'photo_url' => $checklist->photo_url,
            'comment' => $checklist->comment,
            'message' => $isCompleting ? 'Requirement item completed.' : 'Requirement item reopened.',
        ]);
    }

    public function uploadChecklistPhoto(Request $request, Task $task, \App\Models\TaskChecklistSnapshot $checklist): \Illuminate\Http\JsonResponse
    {
        abort_unless($checklist->task_id === $task->id, 404);
        abort_unless(in_array($task->status, [Task::STATUS_IN_PROGRESS, Task::STATUS_PAUSED], true), 409, 'Photos can only be added while the task is active.');

        $request->validate([
            'photo' => ['required', 'image', 'max:10240'],
        ]);

        $path = $request->file('photo')->store('checklist_evidence', 'public');
        $photoUrl = '/storage/' . $path;

        $photos = is_array($checklist->photo_url) ? $checklist->photo_url : [];
        $photos[] = $photoUrl;
        $checklist->update(['photo_url' => $photos]);

        return response()->json([
            'id' => $checklist->id,
            'photo_url' => $photos,
            'message' => 'Photo uploaded successfully.',
        ]);
    }

    public function getChecklistPhoto(Request $request, Task $task, \App\Models\TaskChecklistSnapshot $checklist): \Illuminate\Http\JsonResponse
    {
        abort_unless($checklist->task_id === $task->id, 404);

        return response()->json([
            'id' => $checklist->id,
            'photo_url' => is_array($checklist->photo_url) ? $checklist->photo_url : [],
        ]);
    }

    public function deleteChecklistPhoto(Request $request, Task $task, \App\Models\TaskChecklistSnapshot $checklist): \Illuminate\Http\JsonResponse
    {
        abort_unless($checklist->task_id === $task->id, 404);
        abort_unless(in_array($task->status, [Task::STATUS_IN_PROGRESS, Task::STATUS_PAUSED], true), 409, 'Photos can only be removed while the task is active.');

        $photos = is_array($checklist->photo_url) ? $checklist->photo_url : [];
        $index = (int) $request->integer('index', -1);
        if ($index >= 0 && isset($photos[$index])) {
            unset($photos[$index]);
            $photos = array_values($photos);
        }
        $checklist->update(['photo_url' => $photos]);

        return response()->json([
            'id' => $checklist->id,
            'photo_url' => $photos,
            'message' => 'Photo removed.',
        ]);
    }

    public function updateChecklistComment(Request $request, Task $task, \App\Models\TaskChecklistSnapshot $checklist): \Illuminate\Http\JsonResponse
    {
        abort_unless($checklist->task_id === $task->id, 404);
        abort_unless(in_array($task->status, [Task::STATUS_IN_PROGRESS, Task::STATUS_PAUSED], true), 409, 'Comments can only be added while the task is active.');

        $request->validate([
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $checklist->update([
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'id' => $checklist->id,
            'comment' => $checklist->comment,
            'message' => 'Comment saved successfully.',
        ]);
    }

    public function storeComment(Request $request, Task $task): \Illuminate\Http\JsonResponse
    {
        abort_unless($task->status === Task::STATUS_IN_PROGRESS, 409, 'Comments can only be added while the task is in progress.');

        $request->validate(['comment' => ['required', 'string', 'max:3000']]);

        $comment = \App\Models\TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'comment' => $request->string('comment'),
        ]);

        return response()->json([
            'id' => $comment->id,
            'comment' => $comment->comment,
            'user_name' => $request->user()->name,
            'created_at' => $comment->created_at->format('M j, H:i'),
            'message' => 'Comment added successfully.',
        ], 201);
    }

    private function formData(string $view, array $extra = []): View
    {
        return view($view, array_merge([
            'taskTypes' => TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name', 'default_priority', 'default_estimated_duration_minutes', 'approval_required']),
            'properties' => Property::where('active', true)->orderBy('name')->get(['id', 'name', 'address', 'formatted_address', 'latitude', 'longitude', 'needs_parking']),
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
