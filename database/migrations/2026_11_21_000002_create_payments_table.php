<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedBigInteger('amount_paise');
            $t->char('currency', 3)->default('INR');
            $t->string('purpose', 30)->default('other');   // donation|event_fee|premium|other
            $t->string('gateway', 30)->default('manual');  // razorpay|phonepe|stripe|manual|...
            $t->string('gateway_order_id')->nullable();
            $t->string('gateway_payment_id')->nullable();
            $t->string('status', 20)->default('pending');  // pending|paid|failed|refunded
            $t->string('method', 30)->nullable();
            $t->text('note')->nullable();
            $t->json('meta')->nullable();
            $t->timestamp('paid_at')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index('status');
            $t->index('user_id');
            $t->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
