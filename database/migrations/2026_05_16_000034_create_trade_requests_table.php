<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained('marketplace_listings')->cascadeOnDelete();
            $table->foreignId('offered_card_id')->constrained('cards')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id', 'status']);
            $table->unique(['sender_id', 'listing_id', 'status'], 'trade_requests_unique_pending_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_requests');
    }
};
