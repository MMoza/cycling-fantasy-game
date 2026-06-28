<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scoring_rules', function (Blueprint $table) {
            $table->unsignedTinyInteger('difficulty')->nullable()->after('context');
            $table->unsignedTinyInteger('position')->nullable()->after('difficulty');
            $table->unique(['scoring_system_id', 'type', 'difficulty', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('scoring_rules', function (Blueprint $table) {
            $table->dropUnique(['scoring_system_id', 'type', 'difficulty', 'position']);
            $table->dropColumn('difficulty');
            $table->dropColumn('position');
        });
    }
};
