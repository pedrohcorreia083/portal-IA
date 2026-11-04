<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technical_sheets', function (Blueprint $table) {
            $table->string('new_finish', 30)->nullable()->after('alteration_type');
            $table->boolean('changes_color')->default(false)->after('new_finish');
            $table->string('new_color', 30)->nullable()->after('changes_color');
        });
    }

    public function down(): void
    {
        Schema::table('technical_sheets', function (Blueprint $table) {
            $table->dropColumn(['new_finish', 'changes_color', 'new_color']);
        });
    }
};
