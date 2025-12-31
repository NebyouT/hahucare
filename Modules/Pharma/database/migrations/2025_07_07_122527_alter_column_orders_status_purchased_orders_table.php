<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchased_orders', function (Blueprint $table) {
            $table->string('order_status')->nullable()->after('payment_status')->default('pending')->enum('order_status', ['pending', 'delivered', 'cancelled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchased_orders', function (Blueprint $table) {
            $table->dropColumn('order_status');
        });
    }
};
