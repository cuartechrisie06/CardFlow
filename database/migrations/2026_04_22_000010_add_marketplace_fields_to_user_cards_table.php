<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('acquired_at');
            $table->boolean('is_for_sale')->default(false)->after('is_for_trade');
            $table->decimal('listing_price', 10, 2)->nullable()->after('is_for_sale');
        });
    }

    public function down(): void
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'is_for_sale', 'listing_price']);
        });
    }
};
