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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_single_location')->default(true);
            $table->string('status')->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->jsonb('business_hours')->nullable();
            $table->jsonb('settings')->nullable();
            $table->jsonb('social_links')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('reviews_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create pivot table for business categories
        Schema::create('business_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['business_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_category');
        Schema::dropIfExists('businesses');
    }
}; 