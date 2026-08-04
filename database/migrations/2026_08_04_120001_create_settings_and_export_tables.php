<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single settings store with a scope column (system|organization) —
        // both tables from design.md had identical shape, so they are merged.
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 20)->default('organization'); // system|organization
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['scope', 'key']);
        });

        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->json('filters')->nullable();
            $table->string('status', 20)->default('pending'); // pending|processing|done|failed
            $table->string('file_path')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_jobs');
        Schema::dropIfExists('settings');
    }
};
