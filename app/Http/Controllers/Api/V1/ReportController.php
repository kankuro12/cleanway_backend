<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Services\Attendance\AttendanceRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function shiftsReport(Request $request, AttendanceRules $rules): JsonResponse
    {
        $query = Shift::with(['user:id,name,email,branch_id', 'user.branch:id,name', 'property:id,name', 'events'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->whereHas('user', fn ($uq) => $uq->where('branch_id', $request->integer('branch_id'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('from'), fn ($q) => $q->where('date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($q) => $q->where('date', '<=', $request->string('to')))
            ->orderByDesc('date')
            ->orderByDesc('scheduled_start_at');

        $allShifts = (clone $query)->get();
        $metrics = $rules->generateReportMetrics($allShifts);

        $shifts = $query->paginate($request->integer('per_page', 20));

        $formattedData = $shifts->getCollection()->map(function (Shift $shift) use ($rules) {
            $summary = $rules->summarize($shift);
            $clockIn = $summary['clock_in_event'];
            $clockOut = $summary['clock_out_event'];

            return [
                'id' => $shift->id,
                'user' => [
                    'id' => $shift->user?->id,
                    'name' => $shift->user?->name,
                    'branch' => $shift->user?->branch?->name,
                ],
                'date' => $shift->date->toDateString(),
                'scheduled_start_at' => $shift->scheduled_start_at?->toIso8601String(),
                'scheduled_end_at' => $shift->scheduled_end_at?->toIso8601String(),
                'status' => $shift->status,
                'property' => $shift->property ? ['id' => $shift->property->id, 'name' => $shift->property->name] : null,
                'clock_in' => $clockIn ? [
                    'timestamp' => $clockIn->server_timestamp->toIso8601String(),
                    'inside_geofence' => $clockIn->inside_geofence,
                    'distance_meters' => $clockIn->distance_from_property_meters,
                    'is_office_punch' => $clockIn->task_id === null && $clockIn->property_id === null,
                ] : null,
                'clock_out' => $clockOut ? [
                    'timestamp' => $clockOut->server_timestamp->toIso8601String(),
                    'inside_geofence' => $clockOut->inside_geofence,
                    'distance_meters' => $clockOut->distance_from_property_meters,
                    'is_office_punch' => $clockOut->task_id === null && $clockOut->property_id === null,
                ] : null,
                'metrics' => [
                    'worked_minutes' => $summary['worked_minutes'],
                    'break_minutes' => $summary['break_minutes'],
                    'overtime_minutes' => $summary['overtime_minutes'],
                    'late' => $summary['late'],
                    'early_departure' => $summary['early_departure'],
                    'missed' => $summary['missed'],
                ],
            ];
        });

        return response()->json([
            'metrics' => $metrics,
            'data' => $formattedData,
            'pagination' => [
                'total' => $shifts->total(),
                'per_page' => $shifts->perPage(),
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
            ],
        ]);
    }
}
