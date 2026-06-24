<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_classifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('edition_id');
            $table->string('category');
            $table->uuid('rider_id')->nullable();
            $table->uuid('team_id')->nullable();
            $table->unsignedTinyInteger('position')->nullable();
            $table->timestamps();

            $table->foreign('edition_id')->references('id')->on('editions')->cascadeOnDelete();
            $table->foreign('rider_id')->references('id')->on('riders')->nullOnDelete();
            $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();

            $table->unique(['edition_id', 'category', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_classifications');
    }
};
