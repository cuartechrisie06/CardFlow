<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('user_cards', 'proof_image')) {
                $table->string('proof_image')->nullable();
            }

            if (!Schema::hasColumn('user_cards', 'proof_uploaded_at')) {
                $table->timestamp('proof_uploaded_at')->nullable();
            }

            if (!Schema::hasColumn('user_cards', 'proof_verified')) {
                $table->boolean('proof_verified')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_cards', function (Blueprint $table) {
            if (Schema::hasColumn('user_cards', 'proof_image')) {
                $table->dropColumn('proof_image');
            }

            if (Schema::hasColumn('user_cards', 'proof_uploaded_at')) {
                $table->dropColumn('proof_uploaded_at');
            }

            if (Schema::hasColumn('user_cards', 'proof_verified')) {
                $table->dropColumn('proof_verified');
            }
        });
    }
};