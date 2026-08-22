<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    /**
     * Role-scoped task feed for the dashboard task pane.
     * Admin/supervisor see all tasks; cleaners see only their own.
     */
    private function taskFeed(Request $request, string $tab): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $user = $request->user();

        $query = Task::query()
            ->with(['taskType:id,name', 'property:id,name', 'assignments.assignee']);

        if ($user->role === User::ROLE_CLEANER) {
            $query->forUser($user);
        }

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

        return $query->orderByDesc('scheduled_start_at')->paginate(15)->withQueryString();
    }

    private function taskCounts(Request $request): array
    {
        $user = $request->user();
        $base = Task::query();

        if ($user->role === User::ROLE_CLEANER) {
            $base->forUser($user);
        }

        return [
            'today' => (clone $base)->whereDate('scheduled_start_at', today())->count(),
            'tomorrow' => (clone $base)->whereDate('scheduled_start_at', today()->addDay())->count(),
            'week' => (clone $base)->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'all' => (clone $base)->count(),
        ];
    }

    public function dashboard(Request $request): \Illuminate\View\View|\Illuminate\Http\JsonResponse
    {
        $rawTab = $request->string('tab', 'today')->toString();
        $tab = in_array($rawTab, ['today', 'tomorrow', 'week', 'all', 'filters'], true) ? $rawTab : 'today';

        $user = $request->user();
        $lastEvent = \App\Models\AttendanceEvent::where('user_id', $user->id)
            ->orderByDesc('server_timestamp')
            ->first();

        $todayEvents = \App\Models\AttendanceEvent::where('user_id', $user->id)
            ->whereDate('server_timestamp', today())
            ->orderBy('server_timestamp')
            ->get();

        $workedSecondsToday = 0;
        $currentSegmentStart = null;

        foreach ($todayEvents as $evt) {
            if (in_array($evt->event_type, ['clock_in', 'break_end'], true)) {
                $currentSegmentStart = $evt->server_timestamp;
            } elseif (in_array($evt->event_type, ['break_start', 'clock_out'], true)) {
                if ($currentSegmentStart) {
                    $workedSecondsToday += max(0, $evt->server_timestamp->diffInSeconds($currentSegmentStart));
                    $currentSegmentStart = null;
                }
            }
        }

        $lastType = $lastEvent?->event_type;
        $isPunchedIn = in_array($lastType, ['clock_in', 'break_end'], true);
        if ($isPunchedIn && ! $currentSegmentStart && $lastEvent) {
            $currentSegmentStart = $lastEvent->server_timestamp;
        }

        $activeAnchorMs = ($isPunchedIn && $currentSegmentStart) ? $currentSegmentStart->getTimestampMs() : null;
        $scheduledShiftSeconds = 8 * 3600; // 8 hours standard shift length

        $branch = $user->branch;
        $officeLat = $branch?->latitude ?? config('gps.office_latitude');
        $officeLng = $branch?->longitude ?? config('gps.office_longitude');
        $officeRadius = $branch?->geofence_radius_meters ?? config('gps.office_radius_meters', 100);

        $tasks = $this->taskFeed($request, $tab);

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('partials.task-list', ['tasks' => $tasks])->render(),
                'counts' => $this->taskCounts($request),
            ]);
        }

        return view('dashboard', [
            'tasks' => $tasks,
            'tab' => $tab,
            'counts' => $this->taskCounts($request),
            'lastEvent' => $lastEvent,
            'workedSecondsToday' => $workedSecondsToday,
            'activeAnchorMs' => $activeAnchorMs,
            'scheduledShiftSeconds' => $scheduledShiftSeconds,
            'branch' => $branch,
            'officeLat' => $officeLat,
            'officeLng' => $officeLng,
            'officeRadius' => $officeRadius,
        ]);
    }

    public function users(): \Illuminate\View\View
    {
        return view('pages.settings-users');
    }

    public function personnel(): \Illuminate\View\View
    {
        return view('pages.personnel');
    }

    public function reports(): \Illuminate\View\View
    {
        return view('pages.reports');
    }

    public function approvals(): \Illuminate\View\View
    {
        return view('pages.approvals');
    }

    public function cleanerTools(Request $request): \Illuminate\View\View
    {
        $user = $request->user();
        $lastEvent = \App\Models\AttendanceEvent::where('user_id', $user->id)
            ->orderByDesc('server_timestamp')
            ->first();

        $branch = $user->branch;
        $officeLat = $branch?->latitude ?? config('gps.office_latitude');
        $officeLng = $branch?->longitude ?? config('gps.office_longitude');
        $officeRadius = $branch?->geofence_radius_meters ?? config('gps.office_radius_meters', 100);

        return view('pages.cleaner-tools', [
            'lastEvent' => $lastEvent,
            'branch' => $branch,
            'officeLat' => $officeLat,
            'officeLng' => $officeLng,
            'officeRadius' => $officeRadius,
        ]);
    }
}
