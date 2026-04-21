<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('artist');
            $table->string('title');
            $table->string('edition')->nullable();
            $table->string('album')->nullable();
            $table->string('rarity')->default('Standard');
            $table->decimal('market_value', 10, 2)->default(0);
            $table->string('thumbnail_style')->default('market-thumb-one');
            $table->unsignedInteger('trend_score')->default(0);
            $table->date('released_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
