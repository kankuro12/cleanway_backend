<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Tasks\TransitionTaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function meTasks(Request $request): JsonResponse
    {
        $tasks = Task::query()
            ->with(['taskType:id,name', 'property:id,name', 'assignments', 'subtasks'])
            ->forUser($request->user())
            ->filter($request->only(['status', 'priority', 'from', 'to']))
            ->orderBy('scheduled_start_at')
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => TaskResource::collection($tasks),
            'meta' => ['pagination' => [
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'current_page' => $tasks->currentPage(),
                'last_page' => $tasks->lastPage(),
            ]],
        ]);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        abort_unless($request->user()->hasPermission('4.1'), 403);

        $task->load(['taskType:id,name', 'property:id,name', 'assignments.assignee', 'checklistSnapshot', 'subtasks']);

        return response()->json(['data' => new TaskResource($task)]);
    }

    public function transition(Request $request, Task $task, TransitionTaskStatus $transitioner): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['accepted', 'declined', 'start', 'pause', 'resume', 'complete', 'submit'])],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $statusMap = [
            'accepted' => Task::STATUS_ACCEPTED,
            'declined' => Task::STATUS_DECLINED,
            'start' => Task::STATUS_IN_PROGRESS,
            'pause' => Task::STATUS_PAUSED,
            'resume' => Task::STATUS_IN_PROGRESS,
            'complete' => Task::STATUS_COMPLETED,
            'submit' => Task::STATUS_SUBMITTED_FOR_APPROVAL,
        ];

        try {
            $task = $transitioner->transition($task, $statusMap[$request->input('status')], $request->user(), [
                'remarks' => $request->string('remarks'),
                'latitude' => $request->float('latitude'),
                'longitude' => $request->float('longitude'),
                'source' => 'api',
            ]);
        } catch (\InvalidArgumentException|\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => new TaskResource($task->load('assignments'))]);
    }
}
