<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('variant_name');
            $table->string('variant_type')->nullable();
            $table->string('image_url')->nullable();
            $table->unsignedInteger('community_owned_count')->default(0);
            $table->unsignedInteger('community_listed_count')->default(0);
            $table->decimal('average_trade_value', 10, 2)->default(0);
            $table->decimal('average_sale_price', 10, 2)->default(0);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['card_id', 'variant_name']);
            $table->index('variant_type');
            $table->index('community_owned_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_variants');
    }
};
