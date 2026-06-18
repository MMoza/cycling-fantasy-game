<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stage_results', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('stage_id');
            $table->uuid('rider_id');
            $table->integer('position');
            $table->string('time')->nullable();
            $table->string('gap')->nullable();
            $table->timestamps();

            $table->foreign('stage_id')
                ->references('id')
                ->on('stages')
                ->cascadeOnDelete();

            $table->unique(['stage_id', 'position']);
            $table->index('rider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stage_results');
    }
};
