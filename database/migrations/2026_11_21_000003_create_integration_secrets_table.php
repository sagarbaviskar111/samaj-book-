<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_secrets', function (Blueprint $t) {
            $t->id();
            $t->string('group', 40);
            $t->string('key', 80);
            $t->text('value')->nullable();      // Crypt::encryptString payload
            $t->boolean('is_secret')->default(true);
            $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->unique(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_secrets');
    }
};
