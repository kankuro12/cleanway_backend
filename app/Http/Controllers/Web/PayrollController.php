<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->string('tab', 'today')->toString();

        $baseQuery = Task::with(['property', 'assignments.assignee']);

        // Cleaner role sees their own assigned approved/completed tasks; Admin/Supervisor sees all.
        if ($user->hasRole(2)) {
            $baseQuery->whereHas('assignments', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Include completed and approved tasks
        $baseQuery->whereIn('status', [Task::STATUS_COMPLETED, Task::STATUS_APPROVED]);

        $query = (clone $baseQuery);

        switch ($tab) {
            case 'today':
                $query->whereDate('completed_at', today());
                break;
            case 'yesterday':
                $query->whereDate('completed_at', today()->subDay());
                break;
            case 'week':
                $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'all':
            default:
                break;
        }

        $tasks = $query->latest('completed_at')->get();

        $totalEarned = 0;
        $totalHours = 0;
        $totalExtra = 0;

        $payrollItems = $tasks->map(function ($task) use (&$totalEarned, &$totalHours, &$totalExtra) {
            $property = $task->property;
            $extra = $task->extra_payments ?? [];

            // Cleaner pay: fixed amount wins; otherwise rate × actual (or estimated) hours.
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

            $totalEarned += $taskTotal;
            $totalHours += $hours;
            $totalExtra += $taskExtra;

            return [
                'task' => $task,
                'rate_per_hour' => $rate,
                'fixed_amount' => $fixedAmount,
                'duration_minutes' => $durationMins,
                'hours' => $hours,
                'base_pay' => $basePay,
                'extra_parking' => $extraParking,
                'extra_other' => 0.0,
                'extra_total' => $taskExtra,
                'total_payout' => $taskTotal,
                'completed_at' => $task->completed_at ?? $task->updated_at,
            ];
        });

        $counts = [
            'today' => (clone $baseQuery)->whereDate('completed_at', today())->count(),
            'yesterday' => (clone $baseQuery)->whereDate('completed_at', today()->subDay())->count(),
            'week' => (clone $baseQuery)->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'all' => (clone $baseQuery)->count(),
        ];

        return view('pages.payroll', [
            'tab' => $tab,
            'payrollItems' => $payrollItems,
            'totalEarned' => $totalEarned,
            'totalHours' => $totalHours,
            'totalExtra' => $totalExtra,
            'approvedCount' => $payrollItems->count(),
            'counts' => $counts,
        ]);
    }
}
