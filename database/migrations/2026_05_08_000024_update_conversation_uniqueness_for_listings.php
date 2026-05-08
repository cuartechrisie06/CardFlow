<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('conversations_user_one_id_user_two_id_unique');
            $table->unique(
                ['user_one_id', 'user_two_id', 'marketplace_listing_id'],
                'conversations_pair_listing_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('conversations_pair_listing_unique');
            $table->unique(['user_one_id', 'user_two_id'], 'conversations_user_one_id_user_two_id_unique');
        });
    }
};
