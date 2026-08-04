<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => (int) $this->role,
            'status' => $this->status,
            'employee_no' => $this->employee_no,
            'phone' => $this->phone,
            'employment_type' => $this->employment_type,
            'branch_id' => $this->branch_id,
            'team_id' => $this->team_id,
            'manager_id' => $this->manager_id,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'skills' => $this->skills,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
