<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->boolean('is_listed')->default(false)->after('acquired_at');
            $table->string('marketplace_status')->default('draft')->after('is_listed');
        });

        DB::table('user_cards')
            ->where(function ($query) {
                $query->where('is_public', true)
                    ->orWhere('is_for_trade', true)
                    ->orWhere('is_for_sale', true);
            })
            ->update([
                'is_listed' => true,
                'marketplace_status' => 'active',
            ]);
    }

    public function down(): void
    {
        Schema::table('user_cards', function (Blueprint $table) {
            $table->dropColumn(['is_listed', 'marketplace_status']);
        });
    }
};
