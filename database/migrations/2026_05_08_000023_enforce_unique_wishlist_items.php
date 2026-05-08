<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateGroups = DB::table('wishlist_items')
            ->select('user_id', 'card_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('user_id', 'card_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            DB::table('wishlist_items')
                ->where('user_id', $group->user_id)
                ->where('card_id', $group->card_id)
                ->where('id', '!=', $group->keep_id)
                ->delete();
        }

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->unique(['user_id', 'card_id'], 'wishlist_items_user_card_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropUnique('wishlist_items_user_card_unique');
        });
    }
};
