<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tnmshop_orders', function (Blueprint $table) {
            $table->string('shipping_carrier', 100)->nullable()->after('shipping_address');
            $table->string('tracking_number', 100)->nullable()->after('shipping_carrier');
            $table->timestamp('shipped_at')->nullable()->after('tracking_number');

            $table->index('shipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('tnmshop_orders', function (Blueprint $table) {
            $table->dropIndex(['shipped_at']);
            $table->dropColumn(['shipping_carrier', 'tracking_number', 'shipped_at']);
        });
    }
};
