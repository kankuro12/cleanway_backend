<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

            $table->index(['property_id', 'sort_order']);
        });

        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'cleaning_duration_minutes')) {
                $table->unsignedInteger('cleaning_duration_minutes')->nullable()->after('service_frequency');
            }
            if (! Schema::hasColumn('properties', 'client_fixed_amount')) {
                $table->decimal('client_fixed_amount', 10, 2)->nullable()->after('cleaning_duration_minutes');
            }
            if (! Schema::hasColumn('properties', 'cleaner_pay_type')) {
                $table->string('cleaner_pay_type', 20)->default('per_hour')->after('client_fixed_amount');
            }
            if (! Schema::hasColumn('properties', 'cleaner_fixed_amount')) {
                $table->decimal('cleaner_fixed_amount', 10, 2)->nullable()->after('cleaner_pay_type');
            }
            if (! Schema::hasColumn('properties', 'cleaner_rate_per_hour')) {
                $table->decimal('cleaner_rate_per_hour', 10, 2)->nullable()->after('cleaner_fixed_amount');
            }
            if (! Schema::hasColumn('properties', 'parking_fee')) {
                $table->decimal('parking_fee', 10, 2)->default(0.00)->after('cleaner_rate_per_hour');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'cleaning_duration_minutes', 'client_fixed_amount', 'cleaner_pay_type',
                'cleaner_fixed_amount', 'cleaner_rate_per_hour', 'parking_fee',
            ]);
        });

        Schema::dropIfExists('property_requirements');
    }
};
