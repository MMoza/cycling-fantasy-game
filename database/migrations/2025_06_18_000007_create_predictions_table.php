<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('league_id');
            $table->string('type');
            $table->string('category');
            $table->uuid('stage_id')->nullable();
            $table->json('prediction_value');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('league_id')
                ->references('id')
                ->on('leagues')
                ->cascadeOnDelete();

            $table->foreign('stage_id')
                ->references('id')
                ->on('stages')
                ->nullOnDelete();

            $table->index(['league_id', 'user_id']);
            $table->index(['league_id', 'stage_id']);
            $table->index('locked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
