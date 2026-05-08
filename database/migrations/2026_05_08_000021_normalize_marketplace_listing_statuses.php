<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('marketplace_listings')
            ->where('status', 'inactive')
            ->update(['status' => 'archived']);
    }

    public function down(): void
    {
        DB::table('marketplace_listings')
            ->where('status', 'archived')
            ->update(['status' => 'inactive']);
    }
};
