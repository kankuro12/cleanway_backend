<?php

namespace Database\Seeders;

use App\Domain\Tasks\CreateTask;
use App\Models\ChecklistItem;
use App\Models\ChecklistSection;
use App\Models\ChecklistTemplate;
use App\Models\Property;
use App\Models\TaskRecurrence;
use App\Models\TaskType;
use App\Models\User;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $checklist = ChecklistTemplate::create([
            'name' => 'Standard Office Clean',
            'slug' => ChecklistTemplate::uniqueSlug('Standard Office Clean'),
            'description' => 'Core office cleaning checklist',
            'active' => true,
        ]);

        $section = ChecklistSection::create(['checklist_template_id' => $checklist->id, 'name' => 'Common Areas', 'sort_order' => 0]);
        foreach ([
            ['Empty bins', 'yes_no', true, false],
            ['Vacuum floors', 'pass_fail', true, false],
            ['Wipe desks and surfaces', 'yes_no', true, false],
            ['Restock paper supplies', 'numeric', false, true],
            ['Photo of finished reception', 'photo', true, false],
        ] as $i => [$label, $type, $required, $issue]) {
            ChecklistItem::create([
                'checklist_section_id' => $section->id,
                'label' => $label,
                'item_type' => $type,
                'required' => $required,
                'issue_triggering' => $issue,
                'sort_order' => $i,
            ]);
        }

        $deepClean = TaskType::create([
            'name' => 'Deep Clean',
            'slug' => TaskType::uniqueSlug('Deep Clean'),
            'description' => 'Full deep clean of a property',
            'default_estimated_duration_minutes' => 180,
            'default_priority' => 'high',
            'default_checklist_id' => $checklist->id,
            'before_photo_required' => true,
            'after_photo_required' => true,
            'minimum_photo_count' => 2,
            'approval_required' => true,
            'allowed_assignee_types' => ['user', 'team'],
            'active' => true,
            'sort_order' => 10,
        ]);

        $routine = TaskType::create([
            'name' => 'Routine Clean',
            'slug' => TaskType::uniqueSlug('Routine Clean'),
            'description' => 'Scheduled routine cleaning visit',
            'default_estimated_duration_minutes' => 90,
            'default_priority' => 'medium',
            'after_photo_required' => true,
            'minimum_photo_count' => 1,
            'approval_required' => false,
            'allowed_assignee_types' => ['user'],
            'active' => true,
            'sort_order' => 20,
        ]);

        $supervisor = User::where('role', User::ROLE_SUPERVISOR)->first();
        $cleaner = User::where('role', User::ROLE_CLEANER)->first();
        $property = Property::first();

        if ($cleaner && $property && $supervisor) {
            app(CreateTask::class)->execute([
                'title' => 'Harbourview weekly clean',
                'task_type_id' => $routine->id,
                'property_id' => $property->id,
                'scheduled_start_at' => now()->addDay()->setTime(8, 0)->toDateTimeString(),
                'assignee_type' => 'user',
                'assignee_id' => $cleaner->id,
            ], $supervisor);

            app(CreateTask::class)->execute([
                'title' => 'Quarterly deep clean — Harbourview',
                'task_type_id' => $deepClean->id,
                'property_id' => $property->id,
                'scheduled_start_at' => now()->addDays(5)->setTime(9, 0)->toDateTimeString(),
                'assignee_type' => 'user',
                'assignee_id' => $cleaner->id,
            ], $supervisor);
        }

        if ($property) {
            TaskRecurrence::create([
                'rule' => 'FREQ=WEEKLY;INTERVAL=1',
                'start_date' => now()->toDateString(),
                'time' => '08:00',
                'property_id' => $property->id,
                'assignee_type' => 'user',
                'assignee_id' => $cleaner?->id,
                'task_type_id' => $routine->id,
                'checklist_template_id' => $checklist->id,
                'notification_minutes_before' => 60,
                'active' => true,
                'created_by' => $supervisor?->id,
            ]);
        }
    }
}
