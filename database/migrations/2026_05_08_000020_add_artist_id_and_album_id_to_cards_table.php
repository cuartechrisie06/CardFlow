<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->foreignId('artist_id')->nullable()->after('artist')->constrained()->nullOnDelete();
            $table->foreignId('album_id')->nullable()->after('album')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artist_id');
            $table->dropConstrainedForeignId('album_id');
        });
    }
};
