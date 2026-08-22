<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_subtasks', function (Blueprint $table) {
            if (! Schema::hasColumn('task_subtasks', 'section_name')) {
                $table->string('section_name')->nullable()->default('Property Specific')->after('title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('task_subtasks', function (Blueprint $table) {
            $table->dropColumn(['section_name']);
        });
    }
};
