<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('edition_id');
            $table->integer('number');
            $table->string('name');
            $table->date('date');
            $table->string('type');
            $table->float('distance')->nullable();
            $table->string('origin');
            $table->string('destination');
            $table->string('status')->default('upcoming');
            $table->timestamps();

            $table->foreign('edition_id')
                ->references('id')
                ->on('editions')
                ->cascadeOnDelete();

            $table->unique(['edition_id', 'number']);
            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
