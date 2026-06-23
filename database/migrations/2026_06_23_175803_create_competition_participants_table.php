<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('competition_id');
            $table->uuid('edition_id');
            $table->uuid('team_id');
            $table->uuid('rider_id');
            $table->timestamps();

            $table->foreign('competition_id')->references('id')->on('competitions')->cascadeOnDelete();
            $table->foreign('edition_id')->references('id')->on('editions')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('rider_id')->references('id')->on('riders')->cascadeOnDelete();
            $table->unique(['competition_id', 'edition_id', 'team_id', 'rider_id'], 'participant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
    }
};
