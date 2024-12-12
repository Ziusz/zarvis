<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('status')->default('active')->after('nip');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->timestamp('verified_at')->nullable()->after('is_featured');
        });
    }

    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_featured', 'verified_at']);
        });
    }
}; 