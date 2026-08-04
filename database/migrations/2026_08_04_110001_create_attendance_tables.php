<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('scheduled_start_at');
            $table->timestamp('scheduled_end_at');
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('scheduled'); // scheduled|confirmed|in_progress|completed|missed|cancelled|absent
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
            $table->index('status');
        });

        Schema::create('attendance_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 30); // clock_in|break_start|break_end|clock_out|manual_correction|supervisor_override
            $table->timestamp('server_timestamp')->useCurrent();
            $table->timestamp('device_timestamp')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedInteger('gps_accuracy_meters')->nullable();
            $table->unsignedInteger('effective_radius_meters')->nullable();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('distance_from_property_meters', 10, 2)->nullable();
            $table->boolean('inside_geofence')->nullable();
            $table->string('device_id', 100)->nullable();
            $table->string('source', 20)->default('api');
            $table->boolean('offline')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->string('remarks')->nullable();
            $table->json('integrity_flags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'event_type']);
            $table->index('server_timestamp');
        });

        Schema::create('attendance_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('original_event_id')->constrained('attendance_events')->cascadeOnDelete();
            $table->timestamp('requested_at')->useCurrent();
            $table->string('reason');
            $table->string('decision', 20)->default('pending'); // pending|approved|rejected
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('gps_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('attendance_events')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('policy', 20); // accept|exception|override|reject
            $table->string('reason')->nullable();
            $table->json('integrity_flags')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('resolution_remarks')->nullable();
            $table->timestamps();

            $table->index('policy');
        });

        Schema::create('task_checklist_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('snapshot_item_id')->nullable();
            $table->string('value');
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index('task_id');
        });

        Schema::create('task_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->string('evidence_type', 30); // before|during|after|issue|safety|access_problem|other
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('device_id', 100)->nullable();
            $table->string('source', 20)->default('api');
            $table->string('checksum', 64)->nullable();
            $table->string('processing_status', 20)->default('pending'); // pending|processing|ready|failed
            $table->timestamps();

            $table->index('task_id');
            $table->index(['task_id', 'evidence_type']);
        });

        Schema::create('task_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30); // approve|reject|request_correction|reopen
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('previous_status', 30);
            $table->string('remarks')->nullable();
            $table->string('reason_code', 50)->nullable();
            $table->string('requested_corrections')->nullable();
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->timestamps();

            $table->index(['task_id', 'action']);
        });

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 50); // property_access_problem|missing_key|incorrect_access_code|damaged_equipment|property_damage|safety_hazard|missing_supplies|unsafe_situation|task_cannot_be_completed|other
            $table->string('severity', 20)->default('medium'); // low|medium|high|critical
            $table->text('description');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 20)->default('open'); // open|acknowledged|investigating|resolved|closed
            $table->foreignId('assigned_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category');
        });

        Schema::create('incident_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->string('processing_status', 20)->default('pending');
            $table->timestamps();

            $table->index('incident_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_evidence');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('task_approvals');
        Schema::dropIfExists('task_evidence');
        Schema::dropIfExists('task_checklist_responses');
        Schema::dropIfExists('gps_exceptions');
        Schema::dropIfExists('attendance_correction_requests');
        Schema::dropIfExists('attendance_events');
        Schema::dropIfExists('shifts');
    }
};
