<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('checklist_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_section_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('item_type', 20)->default('yes_no'); // yes_no|pass_fail|text|numeric|photo
            $table->boolean('required')->default(true);
            $table->boolean('issue_triggering')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('default_estimated_duration_minutes')->nullable();
            $table->string('default_priority', 20)->default('medium'); // low|medium|high|critical
            $table->text('default_instructions')->nullable();
            $table->foreignId('default_checklist_id')->nullable()->constrained('checklist_templates')->nullOnDelete();
            $table->boolean('before_photo_required')->default(false);
            $table->boolean('after_photo_required')->default(true);
            $table->unsignedInteger('minimum_photo_count')->default(0);
            $table->boolean('approval_required')->default(false);
            $table->json('allowed_assignee_types')->nullable(); // ['user','team']
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('reference_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('task_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('property_name_snapshot')->nullable();
            $table->string('address_snapshot')->nullable();
            $table->decimal('latitude_snapshot', 10, 7)->nullable();
            $table->decimal('longitude_snapshot', 10, 7)->nullable();
            $table->unsignedInteger('check_in_radius_snapshot')->nullable();
            $table->foreignId('assigned_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->string('priority', 20)->default('medium');
            $table->string('status', 30)->default('draft');
            $table->string('recurrence_rule')->nullable();
            $table->boolean('approval_required')->default(false);
            $table->json('task_type_snapshot')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('scheduled_start_at');
            $table->index('property_id');
            $table->index('task_type_id');
            $table->index('priority');
        });

        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('assignee_type', 20); // user|team
            $table->unsignedBigInteger('assignee_id');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending|accepted|declined
            $table->timestamps();

            $table->unique(['task_id', 'assignee_type', 'assignee_id']);
            $table->index(['assignee_type', 'assignee_id']);
        });

        Schema::create('task_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('remarks')->nullable();
            $table->string('device', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('source', 20)->default('web'); // web|api|system
            $table->timestamps();

            $table->index(['task_id', 'new_status']);
        });

        Schema::create('task_checklist_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('section_name');
            $table->string('item_label');
            $table->string('item_type', 20)->default('yes_no');
            $table->boolean('required')->default(true);
            $table->boolean('issue_triggering')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('task_id');
        });

        Schema::create('task_recurrences', function (Blueprint $table) {
            $table->id();
            $table->string('rule'); // RRULE-style: FREQ=WEEKLY;INTERVAL=1
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->time('time');
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('assignee_type', 20)->nullable();
            $table->unsignedBigInteger('assignee_id')->nullable();
            $table->foreignId('task_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('checklist_template_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('notification_minutes_before')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50);
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('payload')->nullable();
            $table->string('idempotency_key', 100)->nullable()->unique();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 20); // in_app|email|push|sms
            $table->string('status', 20)->default('pending'); // pending|sent|failed|skipped
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();

            $table->index(['channel', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('task_recurrences');
        Schema::dropIfExists('task_checklist_snapshots');
        Schema::dropIfExists('task_status_histories');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_types');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklist_sections');
        Schema::dropIfExists('checklist_templates');
    }
};
