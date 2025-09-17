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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'deposit_amount')) {
                $table->decimal('deposit_amount', 15, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('orders', 'momo_request_id')) {
                $table->string('momo_request_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'momo_order_id')) {
                $table->string('momo_order_id')->nullable()->after('momo_request_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'momo_order_id')) {
                $table->dropColumn('momo_order_id');
            }
            if (Schema::hasColumn('orders', 'momo_request_id')) {
                $table->dropColumn('momo_request_id');
            }
            if (Schema::hasColumn('orders', 'deposit_amount')) {
                $table->dropColumn('deposit_amount');
            }
        });
    }
};
