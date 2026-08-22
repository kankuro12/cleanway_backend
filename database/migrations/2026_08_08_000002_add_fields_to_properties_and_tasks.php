<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'property_code')) {
                $table->string('property_code')->nullable()->after('name');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'hourly_rate')) {
                $table->decimal('hourly_rate', 10, 2)->nullable()->after('estimated_duration_minutes');
            }
            if (! Schema::hasColumn('tasks', 'parking_fee')) {
                $table->decimal('parking_fee', 10, 2)->nullable()->default(0.00)->after('hourly_rate');
            }
            if (! Schema::hasColumn('tasks', 'extra_payments')) {
                $table->json('extra_payments')->nullable()->after('parking_fee');
            }
        });

        Schema::table('task_checklist_snapshots', function (Blueprint $table) {
            if (! Schema::hasColumn('task_checklist_snapshots', 'is_photo_required')) {
                $table->boolean('is_photo_required')->default(false)->after('required');
            }
            if (! Schema::hasColumn('task_checklist_snapshots', 'is_comment_required')) {
                $table->boolean('is_comment_required')->default(false)->after('is_photo_required');
            }
            if (! Schema::hasColumn('task_checklist_snapshots', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('is_comment_required');
            }
            if (! Schema::hasColumn('task_checklist_snapshots', 'comment')) {
                $table->text('comment')->nullable()->after('photo_url');
            }
            if (! Schema::hasColumn('task_checklist_snapshots', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('comment');
            }
            if (! Schema::hasColumn('task_checklist_snapshots', 'completed_by')) {
                $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['property_code']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['hourly_rate', 'parking_fee', 'extra_payments']);
        });

        Schema::table('task_checklist_snapshots', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn(['is_photo_required', 'is_comment_required', 'photo_url', 'comment', 'completed_at', 'completed_by']);
        });
    }
};
