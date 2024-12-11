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
        Schema::create('staff_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->string('status')->default('available'); // available, unavailable, on-leave
            $table->text('notes')->nullable();
            $table->timestamps();

            // Add indexes for common queries
            $table->index(['user_id', 'date']);
            $table->index(['business_id', 'venue_id', 'date']);
            
            // Add unique constraint to prevent duplicate availabilities
            $table->unique(['user_id', 'venue_id', 'date', 'start_time'], 'unique_staff_availability');
        });

        // Create pivot table for staff services
        Schema::create('staff_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'service_id', 'venue_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_service');
        Schema::dropIfExists('staff_availabilities');
    }
}; 