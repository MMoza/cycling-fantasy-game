<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leagues', function (Blueprint $table): void {
            $table->unsignedSmallInteger('max_players')->default(20)->after('invite_code');
            $table->boolean('is_public')->default(false)->after('max_players');

            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table): void {
            $table->dropIndex(['is_public']);
            $table->dropColumn(['max_players', 'is_public']);
        });
    }
};
