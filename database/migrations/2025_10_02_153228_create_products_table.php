<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Código do produto (ex: AN001)
            $table->string('name');
            $table->enum('category', ['A', 'B', 'C', 'CJ', 'PU', 'PI']); // Anel, Broche, Brinco, Colar, Conjunto, Pulseira, Pingente
            $table->text('description')->nullable();
            $table->string('coating')->nullable(); // Dourado, Ródio branco, etc
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
