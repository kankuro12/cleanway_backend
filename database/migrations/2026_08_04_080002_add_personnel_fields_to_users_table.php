<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_no')->nullable()->unique()->after('role');
            $table->string('phone')->nullable()->after('employee_no');
            $table->string('profile_image_path')->nullable()->after('phone');
            $table->json('emergency_contact')->nullable()->after('profile_image_path');
            $table->foreignId('branch_id')->nullable()->after('emergency_contact')->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
            $table->string('employment_type', 30)->nullable()->after('manager_id');
            $table->date('start_date')->nullable()->after('employment_type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->json('skills')->nullable()->after('end_date');
            $table->json('certifications')->nullable()->after('skills');
            $table->json('default_working_hours')->nullable()->after('certifications');
            $table->json('service_areas')->nullable()->after('default_working_hours');
            $table->json('notification_preferences')->nullable()->after('service_areas');
            $table->string('status', 20)->default('active')->index()->after('notification_preferences');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['team_id']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn([
                'employee_no', 'phone', 'profile_image_path', 'emergency_contact',
                'branch_id', 'team_id', 'manager_id', 'employment_type', 'start_date',
                'end_date', 'skills', 'certifications', 'default_working_hours',
                'service_areas', 'notification_preferences', 'status',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
