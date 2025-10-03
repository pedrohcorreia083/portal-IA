<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('product_image')->nullable(); // Foto do produto
            $table->string('identified_product_name')->nullable(); // Nome identificado pela IA
            $table->json('services')->nullable(); // Array de serviços [B, RP, F, L, S, RM]
            $table->enum('coating', ['DO', 'RH', 'RO', 'NE', 'MIX', 'CL'])->nullable();
            $table->enum('status', ['RP', 'SC', 'GTC', 'GCG', 'N'])->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_items');
    }
};
