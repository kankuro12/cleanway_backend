<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(): View
    {
        return view('pages.calendar');
    }

    public function events(Request $request): JsonResponse
    {
        $from = $request->date('from');
        $to = $request->date('to');

        $tasks = Task::query()
            ->with(['assignments', 'taskType:id,name'])
            ->when($request->user()->hasRole(\App\Models\User::ROLE_CLEANER), fn ($q) => $q->forUser($request->user()))
            ->filter($request->only(['status', 'property_id', 'task_type_id', 'assignee_id']))
            ->when($from, fn ($q) => $q->where(fn ($q) => $q->where('scheduled_end_at', '>=', $from->startOfDay())->orWhereNull('scheduled_end_at')))
            ->when($to, fn ($q) => $q->where('scheduled_start_at', '<=', $to->endOfDay()))
            ->whereNotNull('scheduled_start_at')
            ->get(['id', 'uuid', 'title', 'status', 'priority', 'scheduled_start_at', 'scheduled_end_at', 'property_name_snapshot']);

        return response()->json([
            'data' => $tasks->map(fn (Task $task) => [
                'id' => $task->id,
                'uuid' => $task->uuid,
                'title' => $task->title,
                'start' => $task->scheduled_start_at->toIso8601String(),
                'end' => $task->scheduled_end_at?->toIso8601String(),
                'url' => route('tasks.edit', $task),
                'className' => 'fc-task-'.$task->status,
                'extendedProps' => [
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'location' => $task->property_name_snapshot,
                    'assignees' => $task->assignments->count(),
                ],
            ]),
        ]);
    }
}
