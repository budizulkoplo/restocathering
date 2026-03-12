<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catering_orders', function (Blueprint $table) {
            $table->decimal('cash_received', 14, 2)->default(0)->after('total');
            $table->decimal('change_amount', 14, 2)->default(0)->after('cash_received');
        });
    }

    public function down(): void
    {
        Schema::table('catering_orders', function (Blueprint $table) {
            $table->dropColumn(['cash_received', 'change_amount']);
        });
    }
};
