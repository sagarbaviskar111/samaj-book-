<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('phone', 30)->nullable();
            $t->string('event', 20); // login|failed|logout|dev_login
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->timestamp('created_at')->nullable();
            $t->index(['event', 'created_at']);
            $t->index('user_id');
            $t->index('ip');
        });

        Schema::table('audit_logs', function (Blueprint $t) {
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', fn (Blueprint $t) => $t->dropIndex(['created_at']));
        Schema::dropIfExists('login_logs');
    }
};
