<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tnmshop_payment_intents', function (Blueprint $table) {
            $table->id();
            $table->string('intent_id', 40)->unique();
            $table->foreignId('order_id')->constrained('tnmshop_orders')->cascadeOnDelete();
            $table->string('status', 40)->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('THB');
            $table->string('return_url', 2048);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tnmshop_payment_intents');
    }
};
