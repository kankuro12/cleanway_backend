<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Attendance\RecordAttendanceEvent;
use App\Domain\Attendance\SubmitAttendanceCorrection;
use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\Shift;
use App\Services\Attendance\AttendanceRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    private const GPS_RULES = [
        'latitude' => ['nullable', 'numeric', 'between:-90,90'],
        'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        'gps_accuracy_meters' => ['nullable', 'integer', 'min:0', 'max:10000'],
        'device_timestamp' => ['nullable', 'date'],
        'device_id' => ['nullable', 'string', 'max:100'],
        'offline' => ['sometimes', 'boolean'],
        'remarks' => ['nullable', 'string', 'max:1000'],
        'shift_id' => ['nullable', 'exists:shifts,id'],
        'is_mock_location' => ['sometimes', 'boolean'],
    ];

    public function clockIn(Request $request, RecordAttendanceEvent $recorder): JsonResponse
    {
        $request->validate(self::GPS_RULES);
        $event = $recorder->execute($request->user(), AttendanceEvent::TYPE_CLOCK_IN, $request->all());

        return response()->json(['data' => $this->eventPayload($event)], $event->inside_geofence === false ? 202 : 201);
    }

    public function breakStart(Request $request, RecordAttendanceEvent $recorder): JsonResponse
    {
        $request->validate(self::GPS_RULES);
        $event = $recorder->execute($request->user(), AttendanceEvent::TYPE_BREAK_START, $request->all());

        return response()->json(['data' => $this->eventPayload($event)], 201);
    }

    public function breakEnd(Request $request, RecordAttendanceEvent $recorder): JsonResponse
    {
        $request->validate(self::GPS_RULES);
        $event = $recorder->execute($request->user(), AttendanceEvent::TYPE_BREAK_END, $request->all());

        return response()->json(['data' => $this->eventPayload($event)], 201);
    }

    public function clockOut(Request $request, RecordAttendanceEvent $recorder): JsonResponse
    {
        $request->validate(self::GPS_RULES);
        $event = $recorder->execute($request->user(), AttendanceEvent::TYPE_CLOCK_OUT, $request->all());

        return response()->json(['data' => $this->eventPayload($event)], 201);
    }

    public function meShifts(Request $request): JsonResponse
    {
        $shifts = Shift::with(['property:id,name'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('date')
            ->paginate($request->integer('per_page', 25));

        return response()->json([
            'data' => $shifts->map(fn (Shift $shift) => $this->shiftPayload($shift)),
            'meta' => ['pagination' => [
                'total' => $shifts->total(),
                'per_page' => $shifts->perPage(),
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
            ]],
        ]);
    }

    public function requestCorrection(Request $request, SubmitAttendanceCorrection $service): JsonResponse
    {
        $request->validate([
            'original_event_id' => ['required', 'exists:attendance_events,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $original = AttendanceEvent::findOrFail($request->integer('original_event_id'));

        if ($original->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only correct your own attendance events.'], 403);
        }

        $correction = $service->request($request->user(), $original, $request->string('reason'));

        return response()->json(['data' => ['id' => $correction->id, 'decision' => $correction->decision]], 201);
    }

    private function eventPayload(AttendanceEvent $event): array
    {
        return [
            'id' => $event->id,
            'event_type' => $event->event_type,
            'server_timestamp' => $event->server_timestamp?->toIso8601String(),
            'device_timestamp' => $event->device_timestamp?->toIso8601String(),
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'gps_accuracy_meters' => $event->gps_accuracy_meters,
            'effective_radius_meters' => $event->effective_radius_meters,
            'distance_from_property_meters' => $event->distance_from_property_meters,
            'inside_geofence' => $event->inside_geofence,
            'offline' => (bool) $event->offline,
            'integrity_flags' => $event->integrity_flags,
            'exception' => $event->gpsException()->first()?->only(['id', 'policy', 'reason']),
        ];
    }

    private function shiftPayload(Shift $shift): array
    {
        $rules = app(AttendanceRules::class);

        return [
            'id' => $shift->id,
            'date' => $shift->date->toDateString(),
            'scheduled_start_at' => $shift->scheduled_start_at?->toIso8601String(),
            'scheduled_end_at' => $shift->scheduled_end_at?->toIso8601String(),
            'property' => $shift->property ? ['id' => $shift->property->id, 'name' => $shift->property->name] : null,
            'status' => $shift->status,
            'summary' => $rules->summarize($shift),
        ];
    }
}
