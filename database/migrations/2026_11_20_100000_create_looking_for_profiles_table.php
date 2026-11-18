<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('looking_for_profiles', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->string('for_whom', 20);          // son|daughter|brother|sister|cousin|relative|other
            $t->string('looking_for', 10);       // bride|groom
            $t->unsignedTinyInteger('age_min')->nullable();
            $t->unsignedTinyInteger('age_max')->nullable();
            $t->string('education_level', 30)->nullable();
            $t->string('district', 60)->nullable();
            $t->text('note')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('looking_for_profiles');
    }
};
