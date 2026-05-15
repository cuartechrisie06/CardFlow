<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->string('alias_type')->nullable();
            $table->timestamps();

            $table->unique(['card_id', 'alias']);
            $table->index('alias');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_aliases');
    }
};
