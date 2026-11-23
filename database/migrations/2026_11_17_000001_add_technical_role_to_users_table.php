<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * O fluxo de ficha técnica exige um perfil "técnico", mas o enum original
 * só permitia admin/user — o que tornava o perfil inutilizável.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['admin', 'user', 'technical'])->default('user')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('type', 'technical')->update(['type' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['admin', 'user'])->default('user')->change();
        });
    }
};
