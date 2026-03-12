<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->boolean('stock_applied')->default(false)->after('change_amount');
        });

        Schema::table('catering_orders', function (Blueprint $table) {
            $table->boolean('stock_applied')->default(false)->after('balance_due');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_orders', function (Blueprint $table) {
            $table->dropColumn('stock_applied');
        });

        Schema::table('catering_orders', function (Blueprint $table) {
            $table->dropColumn('stock_applied');
        });
    }
};
