<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('condition')->default('Mint');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->timestamp('acquired_at')->nullable();
            $table->boolean('is_for_trade')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
