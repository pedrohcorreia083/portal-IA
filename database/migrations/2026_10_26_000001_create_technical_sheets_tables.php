<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technical_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_item_id')->unique()->constrained('service_order_items')->restrictOnDelete();
            $table->string('status')->default('pending')->index();
            $table->boolean('is_guarantee')->nullable();
            $table->string('treatment')->nullable();
            $table->text('diagnosis')->nullable();
            $table->decimal('repair_price', 10, 2)->nullable();
            $table->boolean('changes_coating')->default(false);
            $table->string('target_coating')->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('delivery_destination')->default('store');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('technical_sheet_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technical_sheet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('from_status');
            $table->string('to_status');
            $table->text('notes')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technical_sheet_events');
        Schema::dropIfExists('technical_sheets');
    }
};
