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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->json('specialties')->nullable()->after('phone');
            $table->text('experience')->nullable()->after('specialties');
            $table->json('languages')->nullable()->after('experience');
            $table->decimal('average_rating', 3, 2)->nullable()->after('languages');
            $table->integer('reviews_count')->default(0)->after('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'specialties',
                'experience',
                'languages',
                'average_rating',
                'reviews_count',
            ]);
        });
    }
}; 