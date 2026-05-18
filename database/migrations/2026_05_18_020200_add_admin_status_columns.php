<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('onboarding_step');
            }
            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('suspended_at');
            }
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('marketplace_listings', 'proof_of_ownership')) {
                $table->string('proof_of_ownership')->nullable()->after('proof_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_listings', 'proof_of_ownership')) {
                $table->dropColumn('proof_of_ownership');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
            if (Schema::hasColumn('users', 'suspended_at')) {
                $table->dropColumn('suspended_at');
            }
        });
    }
};
