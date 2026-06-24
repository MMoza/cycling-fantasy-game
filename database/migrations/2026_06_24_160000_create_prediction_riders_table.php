<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_riders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('prediction_id');
            $table->uuid('rider_id');
            $table->unsignedTinyInteger('position')->nullable()->comment('1-based position in the prediction, null for unordered predictions');
            $table->timestamps();

            $table->foreign('prediction_id')
                ->references('id')
                ->on('predictions')
                ->cascadeOnDelete();

            $table->foreign('rider_id')
                ->references('id')
                ->on('riders')
                ->cascadeOnDelete();

            $table->unique(['prediction_id', 'rider_id']);
            $table->index('prediction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediction_riders');
    }
};
