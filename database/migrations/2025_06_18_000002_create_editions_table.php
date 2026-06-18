<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('competition_id');
            $table->integer('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('upcoming');
            $table->timestamps();

            $table->foreign('competition_id')
                ->references('id')
                ->on('competitions')
                ->cascadeOnDelete();
            $table->unique(['competition_id', 'year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
