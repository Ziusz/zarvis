<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->integer('duration')->comment('Duration in minutes');
            $table->decimal('price', 10, 2);
            $table->integer('capacity')->default(1)->comment('How many customers can book at once');
            $table->json('images')->nullable();
            $table->json('settings')->nullable()->comment('Custom fields, requirements, etc.');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            // Each service should have a unique slug within its business
            $table->unique(['business_id', 'slug']);
        });

        // Pivot table for venue-specific service settings
        Schema::create('service_venue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2)->nullable()->comment('Override business-level price');
            $table->integer('duration')->nullable()->comment('Override business-level duration');
            $table->integer('capacity')->nullable()->comment('Override business-level capacity');
            $table->json('settings')->nullable()->comment('Venue-specific settings');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['service_id', 'venue_id']);
        });

        // Pivot table for staff-service assignments
        Schema::create('service_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Staff member');
            $table->foreignId('venue_id')->nullable()->constrained()->onDelete('cascade')->comment('Optional venue restriction');
            $table->json('settings')->nullable()->comment('Staff-specific service settings');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Staff can provide a service at a specific venue or across all venues
            $table->unique(['service_id', 'user_id', 'venue_id'], 'service_staff_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_staff');
        Schema::dropIfExists('service_venue');
        Schema::dropIfExists('services');
    }
};
