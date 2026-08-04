<?php

namespace App\Domain\Attendance;

use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceEvent;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class SubmitAttendanceCorrection
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function request(User $user, AttendanceEvent $originalEvent, string $reason): AttendanceCorrectionRequest
    {
        $request = DB::transaction(function () use ($user, $originalEvent, $reason): AttendanceCorrectionRequest {
            return AttendanceCorrectionRequest::create([
                'user_id' => $user->id,
                'original_event_id' => $originalEvent->id,
                'reason' => $reason,
            ]);
        });

        $this->audit->log('attendance.correction_requested', 'attendance_correction_request', $request->id, [
            'after' => ['original_event_id' => $originalEvent->id, 'reason' => $reason],
            'actor_id' => $user->id,
        ]);

        return $request;
    }

    /**
     * Corrections never rewrite the original event (spec §11.3): approval
     * writes a new manual_correction event with the corrected values.
     *
     * @param  array<string, mixed>  $corrected
     */
    public function decide(AttendanceCorrectionRequest $request, string $decision, ?User $decider, array $corrected = [], ?string $remarks = null): AttendanceCorrectionRequest
    {
        if (! in_array($decision, [AttendanceCorrectionRequest::DECISION_APPROVED, AttendanceCorrectionRequest::DECISION_REJECTED], true)) {
            throw new \InvalidArgumentException('Decision must be approved or rejected.');
        }

        return DB::transaction(function () use ($request, $decision, $decider, $corrected, $remarks): AttendanceCorrectionRequest {
            $request->update([
                'decision' => $decision,
                'decided_by' => $decider?->id,
                'decided_at' => now(),
                'decision_remarks' => $remarks,
            ]);

            if ($decision === AttendanceCorrectionRequest::DECISION_APPROVED) {
                $original = $request->originalEvent;

                AttendanceEvent::create([
                    'user_id' => $request->user_id,
                    'shift_id' => $original->shift_id,
                    'event_type' => AttendanceEvent::TYPE_MANUAL_CORRECTION,
                    'server_timestamp' => $corrected['server_timestamp'] ?? $original->server_timestamp,
                    'device_timestamp' => $corrected['device_timestamp'] ?? $original->device_timestamp,
                    'latitude' => $corrected['latitude'] ?? $original->latitude,
                    'longitude' => $corrected['longitude'] ?? $original->longitude,
                    'property_id' => $original->property_id,
                    'inside_geofence' => $original->inside_geofence,
                    'source' => 'system',
                    'remarks' => 'Correction of event #'.$original->id.' ('.trim(($remarks ?? '').' '.($request->reason ?? '')).')',
                ]);
            }

            $this->audit->log("attendance.correction_{$decision}", 'attendance_correction_request', $request->id, [
                'after' => ['decision' => $decision],
                'actor_id' => $decider?->id,
            ]);

            return $request->fresh();
        });
    }
}
