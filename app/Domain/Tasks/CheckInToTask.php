<?php

namespace App\Domain\Tasks;

use App\Domain\Attendance\RecordAttendanceEvent;
use App\Models\AttendanceEvent;
use App\Models\GpsException;
use App\Models\Task;
use App\Models\User;

/**
 * GPS-gated task check-in/check-out (spec §12). Delegates event + geofence
 * handling to RecordAttendanceEvent; enforces the policy result.
 *
 * @param  array<string, mixed>  $payload
 */
class CheckInToTask
{
    public function __construct(private readonly RecordAttendanceEvent $recorder) {}

    /**
     * @return array{event: AttendanceEvent, inside_geofence: ?bool, blocked: bool, exception: ?GpsException}
     */
    public function execute(Task $task, User $user, array $payload = []): array
    {
        $payload['task'] = $task;
        $payload['task_id'] = $task->id;
        $payload['source'] ??= 'api';

        $event = $this->recorder->execute($user, AttendanceEvent::TYPE_CLOCK_IN, $payload);

        $exception = $event->gpsException()->first();
        $blocked = $this->isBlocked($event, $exception);

        if (! $blocked && $task->status === Task::STATUS_ASSIGNED) {
            $task->update(['status' => Task::STATUS_ACCEPTED, 'accepted_at' => now()]);
        }

        return ['event' => $event, 'inside_geofence' => $event->inside_geofence, 'blocked' => $blocked, 'exception' => $exception];
    }

    /**
     * @return array{event: AttendanceEvent, inside_geofence: ?bool, blocked: bool, exception: ?GpsException}
     */
    public function executeCheckOut(Task $task, User $user, array $payload = []): array
    {
        $payload['task'] = $task;
        $payload['task_id'] = $task->id;
        $payload['source'] ??= 'api';

        $event = $this->recorder->execute($user, AttendanceEvent::TYPE_CLOCK_OUT, $payload);

        $exception = $event->gpsException()->first();
        $blocked = $this->isBlocked($event, $exception);

        if (! $blocked && $task->status === Task::STATUS_ACCEPTED) {
            $task->update(['status' => Task::STATUS_IN_PROGRESS, 'started_at' => now()]);
        }

        return ['event' => $event, 'inside_geofence' => $event->inside_geofence, 'blocked' => $blocked, 'exception' => $exception];
    }

    private function isBlocked(AttendanceEvent $event, ?GpsException $exception): bool
    {
        if ($event->inside_geofence === false) {
            $policy = $exception?->policy ?? config('gps.out_of_radius_policy');

            if (in_array($policy, [GpsException::POLICY_OVERRIDE, GpsException::POLICY_REJECT], true)) {
                return true;
            }
        }

        if ($exception && $exception->policy === GpsException::POLICY_OVERRIDE) {
            return true;
        }

        return false;
    }
}
