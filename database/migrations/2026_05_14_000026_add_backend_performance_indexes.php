<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->index('artist', 'cards_artist_index');
            $table->index('rarity', 'cards_rarity_index');
            $table->index(['artist', 'album'], 'cards_artist_album_index');
        });

        Schema::table('user_cards', function (Blueprint $table) {
            $table->index(['user_id', 'estimated_value'], 'user_cards_user_estimated_value_index');
            $table->index(['user_id', 'marketplace_status'], 'user_cards_user_marketplace_status_index');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->index('status', 'marketplace_listings_status_index');
            $table->index(['user_id', 'status'], 'marketplace_listings_user_status_index');
            $table->index(['card_id', 'status', 'is_visible'], 'marketplace_listings_card_status_visible_index');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->index(['user_id', 'matched_at'], 'wishlist_items_user_matched_at_index');
            $table->index(['card_id', 'matched_at'], 'wishlist_items_card_matched_at_index');
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->index('status', 'trades_status_index');
            $table->index(['user_id', 'status'], 'trades_user_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropIndex('trades_user_status_index');
            $table->dropIndex('trades_status_index');
        });

        Schema::table('wishlist_items', function (Blueprint $table) {
            $table->dropIndex('wishlist_items_card_matched_at_index');
            $table->dropIndex('wishlist_items_user_matched_at_index');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropIndex('marketplace_listings_card_status_visible_index');
            $table->dropIndex('marketplace_listings_user_status_index');
            $table->dropIndex('marketplace_listings_status_index');
        });

        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropIndex('user_cards_user_marketplace_status_index');
            $table->dropIndex('user_cards_user_estimated_value_index');
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex('cards_artist_album_index');
            $table->dropIndex('cards_rarity_index');
            $table->dropIndex('cards_artist_index');
        });
    }
};
