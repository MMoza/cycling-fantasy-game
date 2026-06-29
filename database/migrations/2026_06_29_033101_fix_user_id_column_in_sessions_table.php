<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sessions', function (Blueprint $table): void {
            $table->string('user_id', 36)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};
