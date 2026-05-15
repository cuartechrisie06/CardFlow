<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title')->index();
            $table->string('member_name')->nullable()->after('artist');
            $table->string('variant_type')->nullable()->after('member_name');
            $table->string('finish')->nullable()->after('variant_type');
            $table->string('official_image_url')->nullable()->after('finish');
            $table->string('catalog_code')->nullable()->after('official_image_url');
        });
    }

    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->dropColumn([
                'slug',
                'member_name',
                'variant_type',
                'finish',
                'official_image_url',
                'catalog_code',
            ]);
        });
    }
};
