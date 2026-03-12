<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('price_unit_quantity', 14, 3)->default(1)->after('latest_purchase_price');
        });

        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->decimal('discount', 14, 2)->default(0)->after('subtotal');
            $table->decimal('cash_received', 14, 2)->default(0)->after('total');
            $table->decimal('change_amount', 14, 2)->default(0)->after('cash_received');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropColumn(['discount', 'cash_received', 'change_amount']);
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['price_unit_quantity']);
        });
    }
};
