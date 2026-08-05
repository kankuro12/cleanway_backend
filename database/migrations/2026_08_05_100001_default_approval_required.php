<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('approval_required')->default(true)->change();
        });

        Schema::table('task_types', function (Blueprint $table) {
            $table->boolean('approval_required')->default(true)->change();
        });

        // Default policy applies to existing records too: completion requires approval.
        DB::table('tasks')->where('approval_required', false)->update(['approval_required' => true]);
        DB::table('task_types')->where('approval_required', false)->update(['approval_required' => true]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('approval_required')->default(false)->change();
        });

        Schema::table('task_types', function (Blueprint $table) {
            $table->boolean('approval_required')->default(false)->change();
        });
    }
};
