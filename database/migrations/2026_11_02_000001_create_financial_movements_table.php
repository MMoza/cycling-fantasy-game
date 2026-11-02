<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 16);
            $table->decimal('amount', 12, 2);
            $table->string('concept', 160);
            $table->string('category', 60);
            $table->date('occurred_on');
            $table->string('source', 16)->default('manual');
            $table->string('external_id')->nullable();
            $table->string('status', 16)->default('confirmed');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('occurred_on');
            $table->index('type');
            $table->index('category');

            // MySQL and SQLite allow repeated NULL external_id values in unique
            // indexes, so manual movements are never blocked by the dedup key.
            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_movements');
    }
};
