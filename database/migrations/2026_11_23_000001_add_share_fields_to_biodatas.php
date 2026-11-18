<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biodatas', function (Blueprint $t) {
            $t->string('share_token', 24)->nullable()->unique();
            $t->boolean('share_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('biodatas', function (Blueprint $t) {
            $t->dropUnique(['share_token']);
            $t->dropColumn(['share_token', 'share_enabled']);
        });
    }
};
