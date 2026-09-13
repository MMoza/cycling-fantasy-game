<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stage_id');
            $table->uuid('rider_id');
            $table->uuid('team_id');
            $table->timestamps();

            $table->foreign('stage_id')->references('id')->on('stages')->cascadeOnDelete();
            $table->foreign('rider_id')->references('id')->on('riders')->cascadeOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->unique(['stage_id', 'rider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_participants');
    }
};
