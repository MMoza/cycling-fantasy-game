<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('badge_code', 64);
            $table->string('event', 16);
            $table->unsignedInteger('level')->default(0);
            $table->unsignedInteger('progress')->default(0);
            $table->json('context')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['user_id', 'badge_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_events');
    }
};
