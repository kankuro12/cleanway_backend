<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_events', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->after('shift_id')->constrained()->nullOnDelete();
            $table->index('task_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_events', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropIndex(['task_id']);
            $table->dropColumn('task_id');
        });
    }
};
