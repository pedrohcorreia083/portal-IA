<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->date('purchase_date')->nullable()->after('observations');
            $table->decimal('piece_value', 10, 2)->nullable()->after('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'piece_value']);
        });
    }
};
