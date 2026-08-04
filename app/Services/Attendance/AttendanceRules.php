<?php

namespace App\Services\Attendance;

use App\Models\AttendanceEvent;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Attendance rules (spec §11.3): late/early, missed shifts, overtime,
 * work/break durations. Read-only computations over immutable events.
 */
class AttendanceRules
{
    public function clockIn(?AttendanceEvent $event): ?Carbon
    {
        return $event?->server_timestamp;
    }

    public function isLate(Shift $shift, ?AttendanceEvent $clockIn, int $graceMinutes = 5): bool
    {
        return $clockIn !== null && $clockIn->server_timestamp->gt($shift->scheduled_start_at->copy()->addMinutes($graceMinutes));
    }

    public function isEarlyDeparture(Shift $shift, ?AttendanceEvent $clockOut, int $graceMinutes = 5): bool
    {
        return $clockOut !== null && $clockOut->server_timestamp->lt($shift->scheduled_end_at->copy()->subMinutes($graceMinutes));
    }

    public function isMissed(Shift $shift): bool
    {
        return $shift->scheduled_end_at->isPast()
            && ! $shift->events()->where('event_type', AttendanceEvent::TYPE_CLOCK_IN)->exists();
    }

    public function workedMinutes(Shift $shift): int
    {
        $clockIn = $shift->events()->where('event_type', AttendanceEvent::TYPE_CLOCK_IN)->first()?->server_timestamp;
        $clockOut = $shift->events()->where('event_type', AttendanceEvent::TYPE_CLOCK_OUT)->latest()->first()?->server_timestamp;

        if (! $clockIn || ! $clockOut) {
            return 0;
        }

        return (int) $clockIn->diffInMinutes($clockOut);
    }

    public function breakMinutes(Shift $shift): int
    {
        $breaks = 0;
        $breakStart = null;

        foreach ($shift->events()->whereIn('event_type', [AttendanceEvent::TYPE_BREAK_START, AttendanceEvent::TYPE_BREAK_END])->orderBy('server_timestamp')->get() as $event) {
            if ($event->event_type === AttendanceEvent::TYPE_BREAK_START) {
                $breakStart = $event->server_timestamp;
            } elseif ($breakStart) {
                $breaks += (int) $breakStart->diffInMinutes($event->server_timestamp);
                $breakStart = null;
            }
        }

        return $breaks;
    }

    public function overtimeMinutes(Shift $shift): int
    {
        $scheduled = $shift->scheduled_start_at->diffInMinutes($shift->scheduled_end_at);

        return max(0, $this->workedMinutes($shift) - (int) $scheduled);
    }

    /**
     * @return array{worked_minutes: int, break_minutes: int, overtime_minutes: int, late: bool, early_departure: bool, missed: bool}
     */
    public function summarize(Shift $shift): array
    {
        $clockIn = $shift->events()->where('event_type', AttendanceEvent::TYPE_CLOCK_IN)->first();

        return [
            'worked_minutes' => $this->workedMinutes($shift),
            'break_minutes' => $this->breakMinutes($shift),
            'overtime_minutes' => $this->overtimeMinutes($shift),
            'late' => $this->isLate($shift, $clockIn),
            'early_departure' => $this->isEarlyDeparture($shift, $shift->events()->where('event_type', AttendanceEvent::TYPE_CLOCK_OUT)->latest()->first()),
            'missed' => $this->isMissed($shift),
        ];
    }
}
