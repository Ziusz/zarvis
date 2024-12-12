<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('venue_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('capacity')->default(1);
            $table->integer('booked')->default(0);
            $table->string('status')->default('available');
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['business_id', 'service_id', 'date']);
            $table->index(['staff_id', 'date']);
            $table->index(['status', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_slots');
    }
}; 