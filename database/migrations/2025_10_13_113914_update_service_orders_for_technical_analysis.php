<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateServiceOrdersForTechnicalAnalysis extends Migration
{
    public function up()
    {
        // Atualizar service_orders
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn('is_guarantee');
            $table->string('status')->default('created')->change();
            $table->foreignId('technical_analyst_id')->nullable()->constrained('users');
            $table->timestamp('analysis_date')->nullable();
        });

        // Atualizar service_order_items
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->boolean('is_guarantee')->nullable();
            $table->decimal('repair_price', 10, 2)->nullable();
            $table->string('technical_status')->nullable();
            $table->text('technical_observations')->nullable();
            $table->string('ai_identified_code')->nullable();
        });
    }

    public function down()
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->string('status')->nullable();
            $table->dropColumn([
                'is_guarantee',
                'repair_price', 
                'technical_status',
                'technical_observations',
                'ai_identified_code'
            ]);
        });

        Schema::table('service_orders', function (Blueprint $table) {
            $table->boolean('is_guarantee')->default(false);
            $table->dropColumn(['technical_analyst_id', 'analysis_date']);
        });
    }
}