<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'onboarding_completed')) {
                $table->boolean('onboarding_completed')->default(false)->after('is_admin');
            }

            if (! Schema::hasColumn('users', 'onboarding_step')) {
                $table->integer('onboarding_step')->default(0)->after('onboarding_completed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'onboarding_step')) {
                $table->dropColumn('onboarding_step');
            }

            if (Schema::hasColumn('users', 'onboarding_completed')) {
                $table->dropColumn('onboarding_completed');
            }
        });
    }
};
