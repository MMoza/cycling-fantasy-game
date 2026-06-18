<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('edition_id');
            $table->uuid('scoring_system_id');
            $table->uuid('owner_id');
            $table->string('invite_code')->unique();
            $table->timestamps();

            $table->foreign('edition_id')
                ->references('id')
                ->on('editions')
                ->cascadeOnDelete();

            $table->foreign('scoring_system_id')
                ->references('id')
                ->on('scoring_systems')
                ->restrictOnDelete();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->index('invite_code');
        });

        Schema::create('league_user', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('league_id');
            $table->uuid('user_id');
            $table->string('role')->default('member');
            $table->timestamps();

            $table->foreign('league_id')
                ->references('id')
                ->on('leagues')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique(['league_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_user');
        Schema::dropIfExists('leagues');
    }
};
