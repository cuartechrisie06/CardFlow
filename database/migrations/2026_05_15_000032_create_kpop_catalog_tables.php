<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpop_idols', function (Blueprint $table) {
            $table->id();
            $table->string('stage_name');
            $table->string('full_name')->nullable();
            $table->string('korean_name')->nullable();
            $table->string('group_name')->nullable();
            $table->date('debut_date')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();

            $table->index('stage_name');
            $table->index('group_name');
            $table->index('company');
        });

        Schema::create('kpop_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('debut_date')->nullable();
            $table->string('company')->nullable();
            $table->unsignedInteger('member_count')->nullable();
            $table->string('gender')->nullable();
            $table->timestamps();

            $table->index('name');
            $table->index('company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpop_groups');
        Schema::dropIfExists('kpop_idols');
    }
};
