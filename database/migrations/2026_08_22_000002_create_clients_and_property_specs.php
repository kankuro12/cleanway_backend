<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Clients Table
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('company_name');
            $table->index('email');
            $table->index('active');
        });

        // 2. Linen Types Table (id, name, rate)
        Schema::create('linen_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 10, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
        });

        // 3. Bed Types Table (id, name)
        Schema::create('bed_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['active', 'sort_order']);
        });

        // 4. Alter Properties Table (client_id, bedrooms, bathrooms, parking specs)
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'client_id')) {
                $table->foreignId('client_id')->nullable()->after('property_category_id')->constrained('clients')->nullOnDelete();
            }
            if (! Schema::hasColumn('properties', 'bedrooms_count')) {
                $table->unsignedSmallInteger('bedrooms_count')->default(0)->after('service_frequency');
            }
            if (! Schema::hasColumn('properties', 'bathrooms_count')) {
                $table->decimal('bathrooms_count', 3, 1)->default(1.0)->after('bedrooms_count');
            }
            if (! Schema::hasColumn('properties', 'parking_type')) {
                $table->string('parking_type', 50)->nullable()->default('none')->after('bathrooms_count');
            }
            if (! Schema::hasColumn('properties', 'parking_spaces_count')) {
                $table->unsignedSmallInteger('parking_spaces_count')->default(0)->after('parking_type');
            }
        });

        // 5. Property Beds (property has multiple beds with qty)
        Schema::create('property_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('bed_type_id')->constrained('bed_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('room_name')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'bed_type_id']);
        });

        // 6. Property Linens (property has multiple linen with qty)
        Schema::create('property_linens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('linen_type_id')->constrained('linen_types')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('custom_rate', 10, 2)->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'linen_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_linens');
        Schema::dropIfExists('property_beds');

        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
            $table->dropColumn(['bedrooms_count', 'bathrooms_count', 'parking_type', 'parking_spaces_count']);
        });

        Schema::dropIfExists('bed_types');
        Schema::dropIfExists('linen_types');
        Schema::dropIfExists('clients');
    }
};
