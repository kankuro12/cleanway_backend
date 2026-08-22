<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Properties: add needs_parking + keep minimal useful fields; remove reliance on google-specific fields? keep for compat but make optional
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'needs_parking')) {
                $table->boolean('needs_parking')->default(false)->after('permitted_check_in_radius_meters');
            }
        });

        // Checklist items: add photo/comment requirement flags (moved from property_requirements)
        Schema::table('checklist_items', function (Blueprint $table) {
            if (! Schema::hasColumn('checklist_items', 'is_photo_required')) {
                $table->boolean('is_photo_required')->default(false)->after('required');
            }
            if (! Schema::hasColumn('checklist_items', 'is_comment_required')) {
                $table->boolean('is_comment_required')->default(false)->after('is_photo_required');
            }
        });

        // Drop property_requirements (now checklist-owned)
        Schema::dropIfExists('property_requirements');
    }

    public function down(): void
    {
        Schema::create('property_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('section_name', 120)->default('General');
            $table->string('label', 1000);
            $table->boolean('is_photo_required')->default(false);
            $table->boolean('is_comment_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'needs_parking')) {
                $table->dropColumn('needs_parking');
            }
        });

        Schema::table('checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('checklist_items', 'is_photo_required')) {
                $table->dropColumn('is_photo_required');
            }
            if (Schema::hasColumn('checklist_items', 'is_comment_required')) {
                $table->dropColumn('is_comment_required');
            }
        });
    }
};
