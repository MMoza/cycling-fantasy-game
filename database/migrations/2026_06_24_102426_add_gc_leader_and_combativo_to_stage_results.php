<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stage_results', function (Blueprint $table) {
            $table->boolean('is_gc_leader')->default(false)->after('gap');
            $table->boolean('is_combativo')->default(false)->after('is_gc_leader');
        });
    }

    public function down(): void
    {
        Schema::table('stage_results', function (Blueprint $table) {
            $table->dropColumn(['is_gc_leader', 'is_combativo']);
        });
    }
};
