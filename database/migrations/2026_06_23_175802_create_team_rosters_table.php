<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_rosters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team_id');
            $table->uuid('rider_id');
            $table->integer('year');
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->foreign('rider_id')->references('id')->on('riders')->cascadeOnDelete();
            $table->unique(['team_id', 'rider_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_rosters');
    }
};
