<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('client_name');
            $table->string('client_document')->nullable(); // CPF/CNPJ
            $table->string('client_phone')->nullable();
            $table->string('consultant_name');
            $table->string('client_code');
            $table->string('origin'); // SHOWROOM SP, SHOWROOM LIMEIRA, SHOWROOM ON-LINE
            $table->date('order_date');
            $table->text('observations')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->boolean('is_guarantee')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
