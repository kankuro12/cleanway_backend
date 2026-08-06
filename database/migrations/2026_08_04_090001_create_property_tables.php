<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('default_check_in_radius_meters')->nullable();
            $table->unsignedBigInteger('default_task_type_id')->nullable();
            $table->unsignedBigInteger('default_checklist_id')->nullable();
            $table->foreignId('default_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('default_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->text('default_safety_instructions')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
        });

        Schema::create('property_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('color', 20)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('address');
            $table->string('formatted_address')->nullable();
            $table->string('google_place_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geocode_accuracy')->nullable();
            $table->string('geocode_status', 30)->default('pending');
            $table->string('geocode_hash', 64)->nullable();
            $table->timestamp('geocoded_at')->nullable();
            $table->string('location_source', 30)->default('unknown');
            $table->unsignedInteger('permitted_check_in_radius_meters')->nullable();
            $table->foreignId('property_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('access_instructions')->nullable();
            $table->text('parking_instructions')->nullable();
            $table->text('safety_instructions')->nullable();
            $table->text('special_cleaning_requirements')->nullable();
            $table->string('service_frequency', 30)->nullable();
            $table->boolean('active')->default(true);
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('address');
            $table->index('google_place_id');
            $table->index('property_category_id');
            $table->index('active');
            $table->index('geocode_status');
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('property_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['property_id', 'property_tag_id']);
        });

        Schema::create('property_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->morphs('assignable');
            $table->string('assignment_role', 30);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index('property_id');
        });

        Schema::create('property_geocode_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('query');
            $table->string('status', 30);
            $table->text('result_json')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            $table->index('property_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_geocode_attempts');
        Schema::dropIfExists('property_assignments');
        Schema::dropIfExists('property_tag');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('property_tags');
        Schema::dropIfExists('property_categories');
    }
};
