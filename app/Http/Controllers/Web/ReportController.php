<?php

namespace App\Http\Controllers\Web;

use App\Domain\Reports\ReportService;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateExport;
use App\Models\ExportJob;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $type = $request->string('type', 'tasks')->toString();
        $filters = $request->only(['from', 'to', 'status', 'priority', 'task_type_id', 'property_id', 'assignee_id', 'user_id', 'geocode_status', 'missing_coords', 'unassigned', 'action', 'category']);

        $report = in_array($type, ['attendance', 'tasks', 'approvals', 'properties', 'incidents'], true)
            ? $reports->run($type, $filters)
            : ['headers' => [], 'rows' => []];

        return view('pages.reports', [
            'type' => $type,
            'report' => $report,
            'exports' => ExportJob::where('requested_by', $request->user()->id)->orderByDesc('id')->limit(10)->get(),
            'workers' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name']),
            'taskTypes' => \App\Models\TaskType::where('active', true)->orderBy('sort_order')->get(['id', 'name']),
            'properties' => \App\Models\Property::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('7.2'), 403);

        $request->validate(['type' => ['required', 'in:attendance,tasks,approvals,properties,incidents']]);

        $job = ExportJob::create([
            'type' => $request->string('type'),
            'filters' => $request->only(['from', 'to', 'status', 'priority', 'task_type_id', 'property_id', 'assignee_id', 'user_id', 'geocode_status', 'missing_coords', 'unassigned', 'action', 'category']),
            'requested_by' => $request->user()->id,
            'requested_at' => now(),
        ]);

        GenerateExport::dispatch($job->id);

        return redirect()->route('reports', ['type' => $job->type])->with('status', 'Export queued — refresh to see progress.');
    }

    public function download(Request $request, ExportJob $job): StreamedResponse
    {
        abort_unless($request->user()->hasPermission('7.2') && $job->requested_by === $request->user()->id, 403);

        abort_unless($job->status === ExportJob::STATUS_DONE && $job->file_path, 404);

        return Storage::disk('evidence')->download($job->file_path, basename($job->file_path));
    }

    public function shiftsReport(Request $request, \App\Services\Attendance\AttendanceRules $rules): View
    {
        abort_unless($request->user()->hasPermission('7.1'), 403);

        $query = \App\Models\Shift::with(['user.branch', 'property', 'events'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('branch_id', $request->integer('branch_id'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($q) => $q->where('date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('date', '<=', $request->string('to')))
            ->orderByDesc('date')
            ->orderByDesc('scheduled_start_at');

        $allShiftsForMetrics = (clone $query)->get();
        $metrics = $rules->generateReportMetrics($allShiftsForMetrics);

        $shifts = $query->paginate(25)->withQueryString();

        return view('pages.reports-shifts', [
            'shifts' => $shifts,
            'metrics' => $metrics,
            'rules' => $rules,
            'workers' => User::orderBy('name')->get(['id', 'name']),
            'branches' => \App\Models\Branch::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function payoutsReport(Request $request): View
    {
        abort_unless($request->user()->hasPermission('7.1') || $request->user()->hasRole(0) || $request->user()->hasRole(1), 403);

        $from = $request->string('from', now()->subMonth()->toDateString())->toString();
        $to = $request->string('to', today()->toDateString())->toString();
        $userId = $request->input('user_id');
        $propertyId = $request->input('property_id');
        $clientId = $request->input('client_id');
        $status = $request->string('status', 'completed_or_approved')->toString();
        $search = $request->string('search')->trim()->toString();

        $query = \App\Models\Task::query()
            ->with([
                'property.client:id,name,company_name',
                'property:id,name,property_code,address,formatted_address,client_id,cleaner_pay_type,cleaner_fixed_amount,cleaner_rate_per_hour',
                'taskType:id,name',
                'assignments.assignee:id,name,email,role',
                'assignedManager:id,name',
            ]);

        // Filter by duration / date range
        if ($from) {
            $query->where(function ($q) use ($from): void {
                $q->where('completed_at', '>=', \Carbon\Carbon::parse($from)->startOfDay())
                  ->orWhere(function ($sq) use ($from): void {
                      $sq->whereNull('completed_at')
                         ->where('scheduled_start_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
                  });
            });
        }
        if ($to) {
            $query->where(function ($q) use ($to): void {
                $q->where('completed_at', '<=', \Carbon\Carbon::parse($to)->endOfDay())
                  ->orWhere(function ($sq) use ($to): void {
                      $sq->whereNull('completed_at')
                         ->where('scheduled_start_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
                  });
            });
        }

        // Status filter
        if ($status === 'completed_or_approved') {
            $query->whereIn('status', [\App\Models\Task::STATUS_COMPLETED, \App\Models\Task::STATUS_APPROVED]);
        } elseif ($status === 'completed') {
            $query->where('status', \App\Models\Task::STATUS_COMPLETED);
        } elseif ($status === 'approved') {
            $query->where('status', \App\Models\Task::STATUS_APPROVED);
        } elseif ($status === 'all') {
            // all statuses
        } else {
            $query->where('status', $status);
        }

        // Worker / Personnel filter
        if ($userId) {
            $query->where(function ($q) use ($userId): void {
                $q->whereHas('assignments', fn ($sq) => $sq->where('assignee_id', $userId))
                  ->orWhere('assigned_manager_id', $userId);
            });
        }

        // Property filter
        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        // Client filter
        if ($clientId) {
            $query->whereHas('property', fn ($pq) => $pq->where('client_id', $clientId));
        }

        // Search filter
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhere('property_name_snapshot', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($pq) use ($search): void {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('property_code', 'like', "%{$search}%")
                         ->orWhere('address', 'like', "%{$search}%");
                  })
                  ->orWhereHas('assignments.assignee', function ($uq) use ($search): void {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $allTasks = $query->orderByDesc('completed_at')->orderByDesc('scheduled_start_at')->get();

        // Calculate payout breakdown item by item
        $totalGrossPayout = 0;
        $totalHours = 0;
        $totalExtra = 0;
        $totalBase = 0;
        $workerSummary = [];

        $payoutRows = $allTasks->map(function ($task) use (&$totalGrossPayout, &$totalHours, &$totalExtra, &$totalBase, &$workerSummary) {
            $property = $task->property;
            $extra = $task->extra_payments ?? [];

            $fixedAmount = isset($extra['cleaner_fixed_amount'])
                ? (float) $extra['cleaner_fixed_amount']
                : (float) ($property?->cleaner_pay_type === 'fixed' ? ($property->cleaner_fixed_amount ?? 0) : 0);

            $rate = $fixedAmount > 0
                ? 0.0
                : (float) ($task->hourly_rate ?? $property?->cleaner_rate_per_hour ?? 45.00);

            $durationMins = $task->estimated_duration_minutes ?? 60;
            if ($task->started_at && $task->completed_at) {
                $durationMins = max(15, (int) round($task->started_at->diffInMinutes($task->completed_at)));
            }

            $hours = round($durationMins / 60, 2);
            $basePay = $fixedAmount > 0 ? $fixedAmount : round($hours * $rate, 2);
            $extraParking = (float) ($extra['parking_fee'] ?? $task->parking_fee ?? 0);
            $taskExtra = $extraParking;
            $taskTotal = round($basePay + $taskExtra, 2);

            $totalGrossPayout += $taskTotal;
            $totalHours += $hours;
            $totalBase += $basePay;
            $totalExtra += $taskExtra;

            // Attribute to assigned cleaners for worker summary
            $assignees = $task->assignments->map(fn ($a) => $a->assignee)->filter();
            if ($assignees->isEmpty() && $task->assignedManager) {
                $assignees = collect([$task->assignedManager]);
            }

            foreach ($assignees as $assignee) {
                $wId = $assignee->id;
                if (! isset($workerSummary[$wId])) {
                    $workerSummary[$wId] = [
                        'worker' => $assignee,
                        'task_count' => 0,
                        'total_hours' => 0,
                        'total_base' => 0,
                        'total_extra' => 0,
                        'total_payout' => 0,
                    ];
                }
                $workerSummary[$wId]['task_count']++;
                $workerSummary[$wId]['total_hours'] += $hours;
                $workerSummary[$wId]['total_base'] += $basePay;
                $workerSummary[$wId]['total_extra'] += $taskExtra;
                $workerSummary[$wId]['total_payout'] += $taskTotal;
            }

            return [
                'task' => $task,
                'property' => $property,
                'assignees' => $assignees,
                'rate_per_hour' => $rate,
                'fixed_amount' => $fixedAmount,
                'pay_type' => $fixedAmount > 0 ? 'Fixed' : 'Hourly ($'.$rate.'/hr)',
                'duration_minutes' => $durationMins,
                'hours' => $hours,
                'base_pay' => $basePay,
                'extra_parking' => $extraParking,
                'extra_total' => $taskExtra,
                'total_payout' => $taskTotal,
                'completed_at' => $task->completed_at ?? $task->scheduled_start_at,
                'status' => $task->status,
                'simplified_status' => $task->simplified_status,
            ];
        });

        // Sort worker summary by total payout desc
        usort($workerSummary, fn ($a, $b) => $b['total_payout'] <=> $a['total_payout']);

        $summary = [
            'total_payout' => $totalGrossPayout,
            'total_hours' => $totalHours,
            'total_tasks' => $allTasks->count(),
            'total_base' => $totalBase,
            'total_extra' => $totalExtra,
            'avg_hourly' => $totalHours > 0 ? round($totalGrossPayout / $totalHours, 2) : 0,
            'active_workers' => count($workerSummary),
        ];

        return view('pages.reports-payouts', [
            'from' => $from,
            'to' => $to,
            'userId' => $userId,
            'propertyId' => $propertyId,
            'clientId' => $clientId,
            'status' => $status,
            'search' => $search,
            'payoutRows' => $payoutRows,
            'workerSummary' => $workerSummary,
            'summary' => $summary,
            'workers' => User::whereIn('role', [User::ROLE_SUPERVISOR, User::ROLE_CLEANER])->orderBy('name')->get(['id', 'name', 'role', 'email']),
            'properties' => \App\Models\Property::with('client:id,name,company_name')->orderBy('name')->get(['id', 'name', 'property_code', 'address', 'client_id']),
            'clients' => \App\Models\Client::where('active', true)->orderBy('name')->get(['id', 'name', 'company_name']),
        ]);
    }
}
