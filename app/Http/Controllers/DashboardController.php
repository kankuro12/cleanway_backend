<?php

namespace App\Http\Controllers;

use App\Domain\Tasks\RescheduleTask;
use App\Models\AttendanceEvent;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    /**
     * Compute estimated drive time in minutes using Haversine formula from origin (branch / office) to property.
     */
    private function calculateDriveTimeMinutes(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?int
    {
        if (! $lat1 || ! $lng1 || ! $lat2 || ! $lng2) {
            return null;
        }

        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceKm = $earthRadiusKm * $c;

        // Estimate ~30 km/h city driving speed + 5 min traffic/parking buffer
        $minutes = ($distanceKm / 30) * 60 + 5;

        return (int) max(5, round($minutes));
    }

    /**
     * Resolve property metadata, status pill, leading icon/dot, and travel time.
     */
    private function resolvePropertyMeta(?Property $property, ?float $officeLat, ?float $officeLng, Collection $tasks): array
    {
        if (! $property) {
            return [
                'id' => null,
                'name' => 'General / One-Off Tasks',
                'header_title' => 'GENERAL / ONE-OFF LOCATIONS',
                'address' => 'Various Locations',
                'code' => 'GEN',
                'status_pill_text' => 'General',
                'status_pill_class' => 'neutral',
                'icon_type' => 'dot',
                'dot_class' => 'gray',
                'drive_time' => null,
                'is_collapsed' => false,
            ];
        }

        $code = $property->property_code ?: ('PROP-'.$property->id);
        $address = $property->address ?: ($property->formatted_address ?: $property->name);
        $headerTitle = strtoupper($address.($code ? " ({$code})" : ''));

        // Category & Tag based status pill resolution
        $catName = strtolower($property->category?->name ?? '');
        $tagNames = $property->tags->pluck('name')->map(fn ($t) => strtolower($t))->all();
        $tagString = implode(' ', $tagNames);

        $pillText = 'Ready';
        $pillClass = 'ready';
        $iconType = 'dot';
        $dotClass = 'green';

        if (str_contains($catName, 'hold') || str_contains($tagString, 'hold') || $tasks->contains(fn ($t) => in_array($t->status, ['delayed', 'paused', 'unable_to_access'], true))) {
            $pillText = 'Hold';
            $pillClass = 'hold';
            $iconType = 'dot';
            $dotClass = 'yellow';
        } elseif (str_contains($catName, 'short') || str_contains($catName, 'guest') || str_contains($catName, 'str') || str_contains($tagString, 'guest') || str_contains($tagString, 'vip') || str_contains($tagString, 'arrival')) {
            $pillText = 'Guest';
            $pillClass = 'guest';
            $iconType = 'guest';
            $dotClass = 'blue';
        } elseif (str_contains($catName, 'turnover') || str_contains($tagString, 'turnover')) {
            $pillText = 'Turnover';
            $pillClass = 'ready';
            $iconType = 'dot';
            $dotClass = 'green';
        } elseif (! empty($property->category?->name)) {
            $pillText = $property->category->name;
            $pillClass = 'neutral';
            $iconType = 'dot';
            $dotClass = 'blue';
        }

        $driveMinutes = $this->calculateDriveTimeMinutes($officeLat, $officeLng, $property->latitude, $property->longitude);
        $driveTimeBadge = $driveMinutes ? "{$driveMinutes}M" : ($property->cleaning_duration_minutes ? "{$property->cleaning_duration_minutes}M" : '30M');

        return [
            'id' => $property->id,
            'name' => $property->name,
            'header_title' => $headerTitle,
            'address' => $address,
            'code' => $code,
            'status_pill_text' => $pillText,
            'status_pill_class' => $pillClass,
            'icon_type' => $iconType,
            'dot_class' => $dotClass,
            'drive_time' => $driveTimeBadge,
            'is_collapsed' => false,
        ];
    }

    /**
     * Compute 4-up Property Ops Stat metrics for the selected scope.
     */
    private function computePropertyOpsStats(Request $request, ?Carbon $targetDate, string $tab): array
    {
        $user = $request->user();
        $base = Task::query();

        $isCleaner = $user->hasRole(User::ROLE_CLEANER) || $user->role === 2;
        if ($isCleaner) {
            $base->forUser($user);
        }

        if ($tab === 'week') {
            $base->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($tab === 'tomorrow') {
            $base->whereDate('scheduled_start_at', today()->addDay());
        } elseif ($tab === 'today') {
            $base->whereDate('scheduled_start_at', today());
        } elseif ($tab === 'all') {
            // no date bound - all dates
        } elseif ($targetDate) {
            $base->whereDate('scheduled_start_at', $targetDate);
        } else {
            $base->whereDate('scheduled_start_at', today());
        }

        $notStartedStatuses = ['draft', 'scheduled', 'unassigned', 'assigned', 'accepted'];
        $inProgressStatuses = ['in_progress', 'paused', 'delayed'];
        $completedStatuses = ['completed', 'submitted_for_approval', 'approved'];
        $issueStatuses = ['unable_to_access', 'correction_requested', 'rejected'];

        $notStarted = (clone $base)->whereIn('status', $notStartedStatuses)->count();
        $inProgress = (clone $base)->whereIn('status', $inProgressStatuses)->count();
        $completed = (clone $base)->whereIn('status', $completedStatuses)->count();
        $issues = (clone $base)->where(function ($q) use ($issueStatuses) {
            $q->whereIn('status', $issueStatuses)->orWhereHas('incidents');
        })->count();

        return [
            'not_started' => $notStarted,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'issues' => $issues,
            'total' => $notStarted + $inProgress + $completed + $issues,
        ];
    }

    public function dashboard(Request $request): \Illuminate\View\View|JsonResponse
    {
        $user = $request->user();
        $isCleaner = $user->hasRole(User::ROLE_CLEANER) || $user->role === 2;

        // Default to current date (today)
        $rawTab = $request->string('tab', 'today')->toString();
        $tab = in_array($rawTab, ['today', 'tomorrow', 'week', 'all'], true) ? $rawTab : 'today';

        $rawDate = $request->string('date')->trim()->toString();
        $targetDate = null;
        if ($tab === 'all') {
            $targetDate = null;
        } elseif (! empty($rawDate)) {
            try {
                $targetDate = Carbon::parse($rawDate)->startOfDay();
            } catch (\Exception $e) {
                $targetDate = today();
            }
        } elseif ($tab === 'tomorrow') {
            $targetDate = today()->addDay();
        } elseif ($tab === 'week') {
            $targetDate = null;
        } else {
            // Default: today
            $targetDate = today();
        }

        $statusFilter = $request->string('status', 'not_started')->toString();
        $search = $request->string('search')->trim()->toString();
        $sort = $request->string('sort', 'suggested')->toString();

        // Attendance state
        $lastEvent = AttendanceEvent::where('user_id', $user->id)
            ->orderByDesc('server_timestamp')
            ->first();

        $todayEvents = AttendanceEvent::where('user_id', $user->id)
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

        // 4-Up Stat counts for the selected scope
        $statCounts = $this->computePropertyOpsStats($request, $targetDate, $tab);

        // Task Query
        $taskQuery = Task::query()
            ->with([
                'taskType:id,name,slug,default_estimated_duration_minutes',
                'property.category:id,name',
                'property.tags:id,name,color',
                'assignments.assignee:id,name,role',
                'evidence:id,task_id',
                'comments:id,task_id',
                'incidents:id,task_id,status',
            ]);

        if ($user->role === User::ROLE_CLEANER) {
            $taskQuery->forUser($user);
        }

        // Date Scope
        if ($tab === 'week') {
            $taskQuery->whereBetween('scheduled_start_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($tab === 'all') {
            // all
        } elseif ($targetDate) {
            $taskQuery->whereDate('scheduled_start_at', $targetDate);
        }

        // Search Filter
        if (! empty($search)) {
            $taskQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('property', fn ($pq) => $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('property_code', 'like', "%{$search}%")
                    )
                    ->orWhereHas('taskType', fn ($tq) => $tq->where('name', 'like', "%{$search}%"));
            });
        }

        // Status Quick Filter from Stat Cards
        if ($statusFilter === 'not_started') {
            $taskQuery->whereIn('status', ['draft', 'scheduled', 'unassigned', 'assigned', 'accepted']);
        } elseif ($statusFilter === 'in_progress') {
            $taskQuery->whereIn('status', ['in_progress', 'paused', 'delayed']);
        } elseif ($statusFilter === 'completed') {
            $taskQuery->whereIn('status', ['completed', 'submitted_for_approval', 'approved']);
        } elseif ($statusFilter === 'issues') {
            $taskQuery->where(function ($q) {
                $q->whereIn('status', ['unable_to_access', 'correction_requested', 'rejected'])
                    ->orWhereHas('incidents');
            });
        }

        // Sorting
        if ($sort === 'time') {
            $taskQuery->orderBy('scheduled_start_at');
        } elseif ($sort === 'priority') {
            $taskQuery->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
                ->orderBy('scheduled_start_at');
        } else {
            // Suggested: Issues & Overdue first, then in-progress, then scheduled start time
            $nowStr = now()->toDateTimeString();
            $taskQuery->orderByRaw("CASE WHEN status IN ('unable_to_access', 'correction_requested', 'rejected') THEN 0 WHEN scheduled_start_at < ? AND status NOT IN ('completed','approved') THEN 1 WHEN status = 'in_progress' THEN 2 ELSE 3 END", [$nowStr])
                ->orderBy('scheduled_start_at');
        }

        $allTasks = $taskQuery->get();

        // Group tasks by Property
        $grouped = $allTasks->groupBy(fn ($t) => $t->property_id ?: 0);

        $propertyGroups = collect();

        foreach ($grouped as $propId => $tasksInGroup) {
            $property = $propId ? $tasksInGroup->first()->property : null;
            $meta = $this->resolvePropertyMeta($property, $officeLat, $officeLng, $tasksInGroup);

            $propertyGroups->push([
                'meta' => $meta,
                'property' => $property,
                'tasks' => $tasksInGroup,
            ]);
        }

        // Sort property groups: Groups with tasks first, sorted by drive time or property name
        $propertyGroups = $propertyGroups->sortBy(function ($g) {
            return $g['meta']['name'];
        })->values();

        $activeDateLabel = $targetDate ? $targetDate->format('M j') : ($tab === 'week' ? 'This Week' : 'All Dates');

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('partials.property-ops-feed', [
                    'propertyGroups' => $propertyGroups,
                    'statusFilter' => $statusFilter,
                ])->render(),
                'stat_counts' => $statCounts,
                'active_date_label' => $activeDateLabel,
                'total_tasks' => $allTasks->count(),
            ]);
        }

        return view('dashboard', [
            'propertyGroups' => $propertyGroups,
            'statCounts' => $statCounts,
            'tab' => $tab,
            'targetDate' => $targetDate ? $targetDate->toDateString() : '',
            'activeDateLabel' => $activeDateLabel,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'sort' => $sort,
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

    /**
     * Inline update due time for a task directly from the dashboard.
     */
    public function updateDueTime(Request $request, Task $task, RescheduleTask $rescheduleTask): JsonResponse
    {
        $request->validate([
            'due_time' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $dueTime = $request->string('due_time')->trim()->toString();
        $dateStr = $request->string('due_date')->trim()->toString();
        
        $baseDate = ! empty($dateStr) 
            ? Carbon::parse($dateStr) 
            : ($task->scheduled_start_at ? $task->scheduled_start_at->copy() : today());

        try {
            $parsedTime = Carbon::parse($dueTime);
            $newStart = $baseDate->copy()->setTime($parsedTime->hour, $parsedTime->minute, 0);
            
            $durationMinutes = $task->estimated_duration_minutes ?: 60;
            $newEnd = $newStart->copy()->addMinutes($durationMinutes);

            $result = $rescheduleTask->execute($task, $newStart, $newEnd, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Schedule updated successfully.',
                'formatted_date' => $newStart->format('M j'),
                'formatted_time' => $newStart->format('g:i A'),
                'time_val' => $newStart->format('g:i'),
                'ampm' => $newStart->format('A'),
                'date_val' => $newStart->format('Y-m-d'),
                'raw_time' => $newStart->format('H:i'),
                'warnings' => $result['warnings'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
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
