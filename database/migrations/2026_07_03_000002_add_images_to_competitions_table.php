<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table): void {
            $table->string('cover_image')->nullable()->after('active');
            $table->string('logo_image')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table): void {
            $table->dropColumn(['cover_image', 'logo_image']);
        });
    }
};
