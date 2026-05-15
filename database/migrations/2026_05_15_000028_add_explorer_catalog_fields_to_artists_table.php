<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name')->unique();
            $table->string('name_original')->nullable()->after('name');
            $table->json('aliases')->nullable()->after('name_original');
            $table->string('agency')->nullable()->after('aliases');
            $table->date('debut_date')->nullable()->after('agency');
            $table->string('logo_url')->nullable()->after('debut_date');
            $table->string('cover_url')->nullable()->after('logo_url');
            $table->boolean('is_active')->default(true)->after('cover_url');
        });
    }

    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'name_original',
                'aliases',
                'agency',
                'debut_date',
                'logo_url',
                'cover_url',
                'is_active',
            ]);
        });
    }
};
