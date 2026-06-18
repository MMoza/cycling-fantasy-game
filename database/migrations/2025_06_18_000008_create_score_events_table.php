<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('league_id');
            $table->uuid('scoring_rule_id');
            $table->integer('points');
            $table->string('description');
            $table->string('context');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('league_id')
                ->references('id')
                ->on('leagues')
                ->cascadeOnDelete();

            $table->index(['league_id', 'user_id']);
            $table->index('context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_events');
    }
};
