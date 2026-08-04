<?php

namespace App\Domain\Properties;

use App\Models\Property;
use App\Models\PropertyAssignment;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class AssignPropertyPersonnel
{
    public function __construct(private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $data  assignable_type, assignable_id, assignment_role, dates, is_primary, reason
     */
    public function execute(Property $property, array $data, ?User $actor = null): PropertyAssignment
    {
        return DB::transaction(function () use ($property, $data, $actor): PropertyAssignment {
            if (! empty($data['is_primary'])) {
                PropertyAssignment::where('property_id', $property->id)
                    ->where('assignment_role', $data['assignment_role'])
                    ->update(['is_primary' => false]);
            }

            $assignment = PropertyAssignment::create($data + [
                'property_id' => $property->id,
                'assigned_by' => $actor?->id,
            ]);

            $this->audit->log('property.assigned', 'property', $property->id, [
                'after' => [
                    'assignment_id' => $assignment->id,
                    'assignable_type' => $assignment->assignable_type,
                    'assignable_id' => $assignment->assignable_id,
                    'assignment_role' => $assignment->assignment_role,
                    'start_date' => $assignment->start_date?->toDateString(),
                    'end_date' => $assignment->end_date?->toDateString(),
                    'is_primary' => $assignment->is_primary,
                ],
                'actor_id' => $actor?->id,
            ]);

            return $assignment;
        });
    }

    public function remove(PropertyAssignment $assignment, ?User $actor = null): void
    {
        DB::transaction(function () use ($assignment, $actor): void {
            $snapshot = $assignment->only(['id', 'assignable_type', 'assignable_id', 'assignment_role']);
            $assignment->delete();

            $this->audit->log('property.assignment_removed', 'property', $assignment->property_id, [
                'before' => $snapshot,
                'actor_id' => $actor?->id,
            ]);
        });
    }
}
