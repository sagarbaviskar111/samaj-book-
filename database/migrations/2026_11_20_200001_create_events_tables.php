<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->string('title', 200);
            $t->text('description')->nullable();
            $t->string('image')->nullable();
            $t->dateTime('starts_at');
            $t->dateTime('ends_at')->nullable();
            $t->string('place', 200)->nullable();
            $t->string('join_url', 500)->nullable();
            $t->string('area')->nullable();
            $t->boolean('is_published')->default(true);
            $t->boolean('registration_open')->default(true);
            $t->boolean('popup_enabled')->default(true);
            $t->unsignedSmallInteger('reminder_interval_min')->default(15);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['is_published', 'starts_at']);
        });

        Schema::create('event_registrations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('event_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->dateTime('joined_at')->nullable();
            $t->timestamps();
            $t->unique(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
        Schema::dropIfExists('events');
    }
};
