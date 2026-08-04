<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Incidents\RaiseIncident;
use App\Domain\Tasks\CheckInToTask;
use App\Domain\Tasks\CompleteTask;
use App\Domain\Tasks\UploadTaskEvidence;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Incident;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskGpsController extends Controller
{
    private const GPS_RULES = [
        'latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'gps_accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:10000'],
        'device_timestamp' => ['nullable', 'date'],
        'device_id' => ['nullable', 'string', 'max:100'],
        'offline' => ['sometimes', 'boolean'],
        'is_mock_location' => ['sometimes', 'boolean'],
    ];

    public function checkIn(Request $request, Task $task, CheckInToTask $service): JsonResponse
    {
        $request->validate(self::GPS_RULES);

        try {
            $result = $service->execute($task, $request->user(), $request->all());
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json([
            'data' => [
                'event_id' => $result['event']->id,
                'inside_geofence' => $result['inside_geofence'],
                'blocked' => $result['blocked'],
                'exception' => $result['exception']?->only(['id', 'policy', 'reason']),
                'task_status' => $task->fresh()->status,
            ],
        ], $result['blocked'] ? 403 : 200);
    }

    public function checkOut(Request $request, Task $task, CheckInToTask $service): JsonResponse
    {
        $request->validate(self::GPS_RULES);

        $result = $service->executeCheckOut($task, $request->user(), $request->all());

        return response()->json([
            'data' => [
                'event_id' => $result['event']->id,
                'inside_geofence' => $result['inside_geofence'],
                'blocked' => $result['blocked'],
                'exception' => $result['exception']?->only(['id', 'policy', 'reason']),
                'task_status' => $task->fresh()->status,
            ],
        ], $result['blocked'] ? 403 : 200);
    }

    public function evidence(Request $request, Task $task, UploadTaskEvidence $uploader): JsonResponse
    {
        $request->validate([
            'evidence' => ['required', 'image', 'max:10240'],
            'evidence_type' => ['required', Rule::in(\App\Models\TaskEvidence::TYPES)],
            'captured_at' => ['nullable', 'date'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'device_id' => ['nullable', 'string', 'max:100'],
        ]);

        $evidence = $uploader->execute(
            $task,
            $request->user(),
            $request->file('evidence'),
            $request->string('evidence_type'),
            $request->only(['captured_at', 'latitude', 'longitude', 'device_id']) + ['source' => 'api'],
        );

        return response()->json([
            'data' => [
                'id' => $evidence->id,
                'evidence_type' => $evidence->evidence_type,
                'original_filename' => $evidence->original_filename,
                'size_bytes' => $evidence->size_bytes,
                'processing_status' => $evidence->processing_status,
            ],
        ], 201);
    }

    public function complete(Request $request, Task $task, CompleteTask $completer): JsonResponse
    {
        $request->validate([
            'responses' => ['sometimes', 'array'],
            'responses.*.snapshot_item_id' => ['required', 'integer'],
            'responses.*.value' => ['required', 'string', 'max:5000'],
            'remarks' => ['sometimes', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $result = $completer->execute($task, $request->user(), $request->input('responses', []), (string) $request->string('remarks'), $request->only(['latitude', 'longitude']) + ['source' => 'api']);

        if (! $result['ok']) {
            return response()->json(['message' => 'Task cannot be completed.', 'errors' => ['task' => $result['missing']]], 422);
        }

        return response()->json(['data' => new TaskResource($task->fresh()->load('assignments'))]);
    }

    public function incidents(Request $request, Task $task, RaiseIncident $raiser): JsonResponse
    {
        $request->validate([
            'category' => ['required', Rule::in(Incident::CATEGORIES)],
            'severity' => ['required', Rule::in(Incident::SEVERITIES)],
            'description' => ['required', 'string', 'max:5000'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $incident = $raiser->execute($request->user(), $request->all() + ['task_id' => $task->id]);

        return response()->json([
            'data' => [
                'id' => $incident->id,
                'uuid' => $incident->uuid,
                'category' => $incident->category,
                'severity' => $incident->severity,
                'status' => $incident->status,
            ],
        ], 201);
    }
}
