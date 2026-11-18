<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biodatas', function (Blueprint $t) {
            $t->string('rashi', 20)->nullable();
            $t->string('nakshatra', 30)->nullable();
            $t->unsignedTinyInteger('nakshatra_pada')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $t) {
            $t->dropColumn(['rashi', 'nakshatra', 'nakshatra_pada']);
        });
    }
};
