<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_events', function (Blueprint $table) {
            $table->uuid('stage_id')->nullable()->after('context');
            $table->index('stage_id');
        });
    }

    public function down(): void
    {
        Schema::table('score_events', function (Blueprint $table) {
            $table->dropIndex(['stage_id']);
            $table->dropColumn('stage_id');
        });
    }
};
