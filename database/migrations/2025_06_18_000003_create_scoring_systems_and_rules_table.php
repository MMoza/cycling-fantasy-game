<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_systems', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('scoring_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('scoring_system_id');
            $table->string('type');
            $table->string('context');
            $table->integer('points');
            $table->timestamps();

            $table->foreign('scoring_system_id')
                ->references('id')
                ->on('scoring_systems')
                ->cascadeOnDelete();

            $table->unique(['scoring_system_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_rules');
        Schema::dropIfExists('scoring_systems');
    }
};
