<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('street_address')->after('description');
            $table->string('city')->after('street_address');
            $table->string('postal_code')->after('city');
            $table->string('nip', 10)->nullable()->after('postal_code')->comment('NIP (Tax Identification Number)');
        });
    }

    public function down()
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['street_address', 'city', 'postal_code', 'nip']);
        });
    }
}; 