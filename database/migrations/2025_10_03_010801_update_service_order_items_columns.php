<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            // Atualizar a coluna status com os novos valores
            $table->enum('status', ['RP', 'SC', 'GTC', 'GCG', 'N'])->nullable()->change();

            // Atualizar a coluna coating com os valores corretos
            $table->enum('coating', ['DO', 'RH', 'RO', 'NE', 'MIX', 'CL'])->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_order_items', function (Blueprint $table) {
            // Reverter para os valores antigos se necessário
            $table->enum('status', ['BP', 'SC', 'CTC', 'CCG', 'N'])->nullable()->change();
            $table->enum('coating', ['DO', 'RH', 'RD', 'NE', 'MIX', 'CL'])->nullable()->change();
        });
    }
};
