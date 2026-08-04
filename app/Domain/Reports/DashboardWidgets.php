<?php

namespace App\Domain\Reports;

use App\Models\AttendanceEvent;
use App\Models\GpsException;
use App\Models\Incident;
use App\Models\Property;
use App\Models\Task;
use App\Models\TaskApproval;
use App\Models\User;
use App\Support\PersonnelScope;
use Illuminate\Support\Carbon;

/**
 * Role-scoped dashboard widgets (spec §16.1–16.3). Minimal columns only.
 */
class DashboardWidgets
{
    public function for(User $user): array
    {
        return match ($user->role) {
            User::ROLE_ADMIN => $this->admin($user),
            User::ROLE_SUPERVISOR => $this->supervisor($user),
            default => $this->cleaner($user),
        };
    }

    private function admin(User $user): array
    {
        return [
            'stats' => [
                ['label' => 'Active tasks', 'value' => Task::whereNotIn('status', [Task::STATUS_APPROVED, Task::STATUS_CANCELLED, Task::STATUS_REJECTED])->count(), 'icon' => 'clipboard-check'],
                ['label' => 'Tasks today', 'value' => Task::whereDate('scheduled_start_at', today())->count(), 'icon' => 'calendar-day'],
                ['label' => 'Overdue', 'value' => Task::where('scheduled_end_at', '<', now())->whereNotIn('status', [Task::STATUS_APPROVED, Task::STATUS_CANCELLED, Task::STATUS_COMPLETED])->count(), 'icon' => 'exclamation-triangle'],
                ['label' => 'Pending approval', 'value' => Task::whereIn('status', [Task::STATUS_SUBMITTED_FOR_APPROVAL, Task::STATUS_CORRECTION_REQUESTED])->count(), 'icon' => 'hourglass-split'],
                ['label' => 'Personnel', 'value' => User::where('status', User::STATUS_ACTIVE)->count(), 'icon' => 'people'],
                ['label' => 'GPS exceptions', 'value' => GpsException::whereNull('resolved_at')->count(), 'icon' => 'geo-alt'],
                ['label' => 'Open incidents', 'value' => Incident::whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_ACKNOWLEDGED, Incident::STATUS_INVESTIGATING])->count(), 'icon' => 'exclamation-octagon'],
                ['label' => 'No-coords properties', 'value' => Property::whereNull('latitude')->where('geocode_status', '!=', Property::GEOCODE_NOT_REQUESTED)->count(), 'icon' => 'building'],
            ],
            'attention' => [
                'GPS exceptions' => GpsException::with(['event.user:id,name'])->whereNull('resolved_at')->latest()->limit(5)->get(),
                'Open incidents' => Incident::with(['reporter:id,name'])->whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_ACKNOWLEDGED, Incident::STATUS_INVESTIGATING])->latest()->limit(5)->get(),
            ],
            'today' => Task::with(['assignments.assignee'])->whereDate('scheduled_start_at', today())->orderBy('scheduled_start_at')->limit(10)->get(['id', 'title', 'status', 'priority', 'scheduled_start_at', 'reference_number']),
        ];
    }

    private function supervisor(User $user): array
    {
        $team = PersonnelScope::apply(User::query(), $user)->pluck('id');
        $tasks = Task::whereHas('assignments', fn ($q) => $q->whereIn('assignee_id', $team))->whereNotIn('status', [Task::STATUS_APPROVED, Task::STATUS_CANCELLED]);

        return [
            'stats' => [
                ['label' => 'Team tasks', 'value' => (clone $tasks)->count(), 'icon' => 'clipboard-check'],
                ['label' => 'Awaiting approval', 'value' => (clone $tasks)->whereIn('status', [Task::STATUS_SUBMITTED_FOR_APPROVAL, Task::STATUS_CORRECTION_REQUESTED])->count(), 'icon' => 'hourglass-split'],
                ['label' => 'Overdue', 'value' => (clone $tasks)->where('scheduled_end_at', '<', now())->count(), 'icon' => 'exclamation-triangle'],
                ['label' => 'Late today', 'value' => AttendanceEvent::whereIn('user_id', $team)->whereDate('server_timestamp', today())->where('event_type', AttendanceEvent::TYPE_CLOCK_IN)->count(), 'icon' => 'clock'],
            ],
            'attention' => [
                'Team incidents' => Incident::with(['reporter:id,name'])->where('reporter_id', '!=', $user->id)->whereIn('status', [Incident::STATUS_OPEN, Incident::STATUS_ACKNOWLEDGED])->latest()->limit(5)->get(),
            ],
            'today' => (clone $tasks)->with(['assignments.assignee'])->whereDate('scheduled_start_at', today())->orderBy('scheduled_start_at')->limit(10)->get(['id', 'title', 'status', 'priority', 'scheduled_start_at', 'reference_number']),
        ];
    }

    private function cleaner(User $user): array
    {
        $today = Task::forUser($user)
            ->with(['assignments.assignee'])
            ->whereDate('scheduled_start_at', today())
            ->orderBy('scheduled_start_at')
            ->get(['id', 'title', 'status', 'priority', 'scheduled_start_at', 'reference_number', 'property_name_snapshot']);

        $next = Task::forUser($user)
            ->where('scheduled_start_at', '>=', now())
            ->whereNotIn('status', [Task::STATUS_APPROVED, Task::STATUS_CANCELLED, Task::STATUS_COMPLETED])
            ->orderBy('scheduled_start_at')
            ->first();

        $pending = \App\Models\Notification::where('user_id', $user->id)->unread()->count();
        $todayCount = $today->count();
        $todayDone = $today->whereIn('status', [Task::STATUS_APPROVED, Task::STATUS_COMPLETED])->count();

        return [
            'stats' => [
                ['label' => 'Tasks today', 'value' => $todayCount, 'icon' => 'calendar-day'],
                ['label' => 'Completed', 'value' => $todayDone, 'icon' => 'check2-circle'],
                ['label' => 'Unread alerts', 'value' => $pending, 'icon' => 'bell'],
            ],
            'attention' => [
                'Your corrections' => \App\Models\AttendanceCorrectionRequest::with(['originalEvent'])->where('user_id', $user->id)->where('decision', 'pending')->latest()->limit(5)->get(),
            ],
            'today' => $today,
            'next' => $next,
        ];
    }
}
