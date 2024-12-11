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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('capacity')->default(1);
            $table->integer('booked')->default(0);
            $table->boolean('is_available')->default(true);
            $table->string('status')->default('available'); // available, fully-booked, blocked
            $table->text('notes')->nullable();
            $table->timestamps();

            // Add indexes for common queries
            $table->index(['business_id', 'venue_id', 'date']);
            $table->index(['service_id', 'date']);
            $table->index(['staff_id', 'date']);
            
            // Add unique constraint to prevent duplicate slots
            $table->unique(['venue_id', 'service_id', 'staff_id', 'date', 'start_time'], 'unique_time_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
}; 