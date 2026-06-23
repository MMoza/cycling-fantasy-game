<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stages', function (Blueprint $table): void {
            $table->dateTime('scheduled_start')->nullable()->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table): void {
            $table->dropColumn('scheduled_start');
        });
    }
};
