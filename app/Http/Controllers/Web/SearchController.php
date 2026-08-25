<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Incident;
use App\Models\Property;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Unified system-wide search across tasks, properties, clients, personnel, and incidents.
     */
    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString();
        $scope = $request->string('scope', 'properties')->toString();
        $propertyCode = $request->string('property_code')->trim()->toString();
        $taskCode = $request->string('task_code')->trim()->toString();
        $taskType = $request->string('task_type')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $priority = $request->string('priority')->trim()->toString();
        $user = $request->user();

        $tasks = collect();
        $properties = collect();
        $personnel = collect();
        $clients = collect();
        $incidents = collect();

        $hasSearch = mb_strlen($q) >= 1 || mb_strlen($propertyCode) >= 1 || mb_strlen($taskCode) >= 1 || mb_strlen($taskType) >= 1 || mb_strlen($status) >= 1;

        if ($hasSearch) {
            // 1. Properties
            if (in_array($scope, ['all', 'properties'], true) && $user->hasPermission('3.1') && ! $user->hasRole(User::ROLE_CLEANER)) {
                $propQuery = Property::query()->with(['category:id,name', 'client:id,name,company_name'])
                    ->withCount('tasks');

                if (! empty($q)) {
                    $propQuery->where(function ($sub) use ($q): void {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('property_code', 'like', "%{$q}%")
                            ->orWhere('address', 'like', "%{$q}%")
                            ->orWhere('formatted_address', 'like', "%{$q}%")
                            ->orWhere('contact_name', 'like', "%{$q}%")
                            ->orWhere('contact_phone', 'like', "%{$q}%")
                            ->orWhereHas('client', function ($cq) use ($q): void {
                                $cq->where('name', 'like', "%{$q}%")
                                   ->orWhere('company_name', 'like', "%{$q}%");
                            })
                            ->orWhereHas('tasks', function ($tq) use ($q): void {
                                $tq->where('title', 'like', "%{$q}%")
                                   ->orWhere('reference_number', 'like', "%{$q}%");
                            });
                    });
                }

                if (! empty($propertyCode)) {
                    $propQuery->where('property_code', 'like', "%{$propertyCode}%");
                }

                $properties = $propQuery->orderBy('name')->limit(30)->get();
            }

            // 2. Tasks — Ordered by Property, Status, Date Time
            if (in_array($scope, ['all', 'properties', 'tasks'], true)) {
                $matchedPropertyIds = $properties->pluck('id')->all();

                $taskQuery = Task::query()
                    ->with(['property.client:id,name,company_name', 'property:id,name,address,property_code,client_id', 'taskType:id,name', 'assignments.assignee:id,name']);

                $taskQuery->where(function ($query) use ($q, $propertyCode, $taskCode, $taskType, $matchedPropertyIds): void {
                    if (! empty($matchedPropertyIds)) {
                        $query->whereIn('property_id', $matchedPropertyIds);
                    }

                    if (! empty($q)) {
                        $query->orWhere(function ($sub) use ($q): void {
                            $sub->where('title', 'like', "%{$q}%")
                                ->orWhere('reference_number', 'like', "%{$q}%")
                                ->orWhere('description', 'like', "%{$q}%")
                                ->orWhere('property_name_snapshot', 'like', "%{$q}%")
                                ->orWhere('address_snapshot', 'like', "%{$q}%")
                                ->orWhereHas('property', function ($pq) use ($q): void {
                                    $pq->where('name', 'like', "%{$q}%")
                                        ->orWhere('property_code', 'like', "%{$q}%")
                                        ->orWhere('address', 'like', "%{$q}%")
                                        ->orWhere('formatted_address', 'like', "%{$q}%");
                                })
                                ->orWhereHas('property.client', function ($cq) use ($q): void {
                                    $cq->where('name', 'like', "%{$q}%")
                                       ->orWhere('company_name', 'like', "%{$q}%");
                                })
                                ->orWhereHas('taskType', function ($tq) use ($q): void {
                                    $tq->where('name', 'like', "%{$q}%");
                                });
                        });
                    }

                    if (! empty($propertyCode)) {
                        $query->orWhere(function ($sub) use ($propertyCode): void {
                            $sub->whereHas('property', fn ($pq) => $pq->where('property_code', 'like', "%{$propertyCode}%"))
                                ->orWhere('property_name_snapshot', 'like', "%{$propertyCode}%");
                        });
                    }

                    if (! empty($taskCode)) {
                        $query->orWhere('reference_number', 'like', "%{$taskCode}%");
                    }

                    if (! empty($taskType)) {
                        $query->orWhereHas('taskType', fn ($tq) => $tq->where('name', 'like', "%{$taskType}%"));
                    }
                });

                if (! empty($status)) {
                    $taskQuery->filter(['status' => $status]);
                }

                if (! empty($priority)) {
                    $taskQuery->where('priority', $priority);
                }

                if ($user->role === User::ROLE_CLEANER) {
                    $taskQuery->forUser($user);
                }

                // Order by Property, Status (In Progress -> Not Started -> Completed -> Cancelled), and Date Time
                $tasks = $taskQuery
                    ->leftJoin('properties', 'tasks.property_id', '=', 'properties.id')
                    ->select('tasks.*')
                    ->orderByRaw('COALESCE(properties.name, tasks.property_name_snapshot) ASC')
                    ->orderByRaw("CASE 
                        WHEN tasks.status IN ('in_progress', 'paused') THEN 1 
                        WHEN tasks.status IN ('not_started', 'draft', 'scheduled', 'unassigned', 'assigned', 'accepted') THEN 2 
                        WHEN tasks.status IN ('completed', 'submitted_for_approval', 'approved') THEN 3 
                        ELSE 4 END ASC")
                    ->orderByDesc('tasks.scheduled_start_at')
                    ->limit(50)
                    ->get();
            }

            // 3. Personnel
            if (in_array($scope, ['all', 'personnel'], true) && $user->hasPermission('2.1') && empty($propertyCode) && empty($taskCode) && empty($taskType)) {
                $personnel = User::query()
                    ->where(function ($sub) use ($q): void {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    })
                    ->latest('id')
                    ->limit(15)
                    ->get();
            }

            // 4. Clients
            if (in_array($scope, ['all', 'clients'], true) && $user->hasPermission('3.1') && ! $user->hasRole(User::ROLE_CLEANER) && empty($taskCode) && empty($taskType)) {
                $clients = Client::query()
                    ->where(function ($sub) use ($q): void {
                        $sub->where('name', 'like', "%{$q}%")
                            ->orWhere('company_name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    })
                    ->latest('id')
                    ->limit(15)
                    ->get();
            }

            // 5. Incidents
            if (in_array($scope, ['all', 'incidents'], true) && $user->hasPermission('8.1') && empty($propertyCode) && empty($taskType)) {
                $incidents = Incident::query()
                    ->where(function ($sub) use ($q): void {
                        $sub->where('title', 'like', "%{$q}%")
                            ->orWhere('reference_number', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    })
                    ->latest('id')
                    ->limit(15)
                    ->get();
            }
        }

        $totalResults = $tasks->count() + $properties->count() + $personnel->count() + $clients->count() + $incidents->count();

        // Group tasks by Property
        $tasksByProperty = $tasks->groupBy(function ($task) {
            return $task->property_id ? (string) $task->property_id : 'unassigned_'.$task->id;
        });

        return view('pages.search', [
            'q' => $q,
            'scope' => $scope,
            'propertyCode' => $propertyCode,
            'taskCode' => $taskCode,
            'taskType' => $taskType,
            'status' => $status,
            'priority' => $priority,
            'hasSearch' => $hasSearch,
            'tasks' => $tasks,
            'tasksByProperty' => $tasksByProperty,
            'properties' => $properties,
            'personnel' => $personnel,
            'clients' => $clients,
            'incidents' => $incidents,
            'totalResults' => $totalResults,
        ]);
    }
}
