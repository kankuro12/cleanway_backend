<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'worked_seconds')) {
                $table->unsignedInteger('worked_seconds')->default(0)->after('estimated_duration_minutes');
            }
            if (! Schema::hasColumn('tasks', 'last_resume_at')) {
                $table->timestamp('last_resume_at')->nullable()->after('worked_seconds');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'last_resume_at')) {
                $table->dropColumn('last_resume_at');
            }
            if (Schema::hasColumn('tasks', 'worked_seconds')) {
                $table->dropColumn('worked_seconds');
            }
        });
    }
};
