<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('proof_photo')->nullable()->after('is_visible');
            $table->boolean('proof_verified')->default(false)->after('proof_photo');
            $table->string('proof_status')->nullable()->after('proof_verified');
            $table->unsignedTinyInteger('proof_score')->nullable()->after('proof_status');

            $table->index(
                ['proof_status', 'proof_verified'],
                'marketplace_listings_proof_status_verified_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropIndex('marketplace_listings_proof_status_verified_index');
            $table->dropColumn([
                'proof_photo',
                'proof_verified',
                'proof_status',
                'proof_score',
            ]);
        });
    }
};
