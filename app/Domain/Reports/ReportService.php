<?php

namespace App\Domain\Reports;

use Illuminate\Support\Carbon;

/**
 * Report queries (spec §16.4). Each report returns rows + headers for the
 * export writer; web views consume the same data. Filters are whitelisted
 * per report and applied without pulling unnecessary columns.
 */
class ReportService
{
    /**
     * @return array<string, mixed>
     */
    public function attendance(array $filters): array
    {
        $from = $filters['from'] ?? today()->subMonth()->toDateString();
        $to = $filters['to'] ?? today()->toDateString();
        $userId = $filters['user_id'] ?? null;

        $rows = \App\Models\AttendanceEvent::query()
            ->with('user:id,name')
            ->whereBetween('server_timestamp', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderBy('server_timestamp')
            ->get(['id', 'user_id', 'event_type', 'server_timestamp', 'inside_geofence', 'distance_from_property_meters', 'source', 'offline']);

        return [
            'headers' => ['Event ID', 'User', 'Type', 'Server time', 'Inside geofence', 'Distance (m)', 'Source', 'Offline'],
            'rows' => $rows->map(fn ($e) => [
                $e->id,
                $e->user?->name,
                $e->event_type,
                $e->server_timestamp?->toDateTimeString(),
                $e->inside_geofence === null ? '' : ($e->inside_geofence ? 'yes' : 'no'),
                $e->distance_from_property_meters,
                $e->source,
                $e->offline ? 'yes' : 'no',
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tasks(array $filters): array
    {
        $rows = \App\Models\Task::query()
            ->with(['taskType:id,name', 'property:id,name'])
            ->filter(array_intersect_key($filters, array_flip(['status', 'priority', 'task_type_id', 'property_id', 'assignee_id'])))
            ->when($filters['from'] ?? null, fn ($q) => $q->where('scheduled_start_at', '>=', $filters['from']))
            ->when($filters['to'] ?? null, fn ($q) => $q->where('scheduled_start_at', '<=', $filters['to']))
            ->orderByDesc('scheduled_start_at')
            ->limit(5000)
            ->get(['id', 'reference_number', 'title', 'status', 'priority', 'task_type_id', 'property_id', 'scheduled_start_at', 'completed_at', 'approved_at', 'estimated_duration_minutes']);

        return [
            'headers' => ['Ref', 'Title', 'Status', 'Priority', 'Type', 'Property', 'Scheduled', 'Completed', 'Approved', 'Est. minutes'],
            'rows' => $rows->map(fn ($t) => [
                $t->reference_number,
                $t->title,
                $t->status,
                $t->priority,
                $t->taskType?->name,
                $t->property?->name,
                $t->scheduled_start_at?->toDateTimeString(),
                $t->completed_at?->toDateTimeString(),
                $t->approved_at?->toDateTimeString(),
                $t->estimated_duration_minutes,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function approvals(array $filters): array
    {
        $rows = \App\Models\TaskApproval::query()
            ->with(['task:id,title,reference_number', 'reviewer:id,name'])
            ->when($filters['action'] ?? null, fn ($q) => $q->where('action', $filters['action']))
            ->when($filters['from'] ?? null, fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'] ?? null, fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->orderByDesc('id')
            ->limit(5000)
            ->get(['id', 'task_id', 'action', 'reviewer_id', 'previous_status', 'remarks', 'quality_score', 'created_at']);

        return [
            'headers' => ['Approval ID', 'Task', 'Action', 'Reviewer', 'Previous status', 'Remarks', 'Quality', 'At'],
            'rows' => $rows->map(fn ($a) => [
                $a->id,
                $a->task?->reference_number.' — '.$a->task?->title,
                $a->action,
                $a->reviewer?->name,
                $a->previous_status,
                $a->remarks,
                $a->quality_score,
                $a->created_at?->toDateTimeString(),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function properties(array $filters): array
    {
        $rows = \App\Models\Property::query()
            ->with(['category:id,name'])
            ->when($filters['geocode_status'] ?? null, fn ($q) => $q->where('geocode_status', $filters['geocode_status']))
            ->when(! empty($filters['missing_coords']), fn ($q) => $q->whereNull('latitude'))
            ->when(! empty($filters['unassigned']), fn ($q) => $q->whereDoesntHave('assignments'))
            ->orderBy('name')
            ->limit(5000)
            ->get(['id', 'name', 'address', 'property_category_id', 'geocode_status', 'latitude', 'longitude', 'active', 'service_frequency']);

        return [
            'headers' => ['ID', 'Name', 'Address', 'Category', 'Geocode', 'Lat', 'Lng', 'Active', 'Frequency'],
            'rows' => $rows->map(fn ($p) => [
                $p->id,
                $p->name,
                $p->address,
                $p->category?->name,
                $p->geocode_status,
                $p->latitude,
                $p->longitude,
                $p->active ? 'yes' : 'no',
                $p->service_frequency,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function incidents(array $filters): array
    {
        $rows = \App\Models\Incident::query()
            ->with(['reporter:id,name', 'task:id,reference_number'])
            ->when($filters['status'] ?? null, fn ($q) => $q->where('status', $filters['status']))
            ->when($filters['category'] ?? null, fn ($q) => $q->where('category', $filters['category']))
            ->when($filters['from'] ?? null, fn ($q) => $q->whereDate('created_at', '>=', $filters['from']))
            ->when($filters['to'] ?? null, fn ($q) => $q->whereDate('created_at', '<=', $filters['to']))
            ->orderByDesc('id')
            ->limit(5000)
            ->get(['id', 'task_id', 'reporter_id', 'category', 'severity', 'status', 'description', 'created_at', 'resolved_at']);

        return [
            'headers' => ['ID', 'Task', 'Reporter', 'Category', 'Severity', 'Status', 'Description', 'Raised', 'Resolved'],
            'rows' => $rows->map(fn ($i) => [
                $i->id,
                $i->task?->reference_number,
                $i->reporter?->name,
                $i->category,
                $i->severity,
                $i->status,
                \Illuminate\Support\Str::limit($i->description, 80),
                $i->created_at?->toDateTimeString(),
                $i->resolved_at?->toDateTimeString(),
            ])->all(),
        ];
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>}
     */
    public function run(string $type, array $filters): array
    {
        return match ($type) {
            'attendance' => $this->attendance($filters),
            'tasks' => $this->tasks($filters),
            'approvals' => $this->approvals($filters),
            'properties' => $this->properties($filters),
            'incidents' => $this->incidents($filters),
            default => throw new \InvalidArgumentException("Unknown report type: {$type}"),
        };
    }
}
