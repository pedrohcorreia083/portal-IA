<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['admin', 'user'])->default('user');
            $table->string('company_name')->nullable();
            $table->string('cnpj', 18)->nullable();
            $table->string('last_name');
            $table->boolean('is_active')->default(true);
            $table->json('permissions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'company_name',
                'cnpj',
                'last_name',
                'is_active',
                'permissions'
            ]);
        });
    }
};
