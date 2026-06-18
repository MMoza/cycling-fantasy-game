<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('last_visited_league_id')->nullable()->after('remember_token');

            $table->foreign('last_visited_league_id')
                ->references('id')
                ->on('leagues')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['last_visited_league_id']);
            $table->dropColumn('last_visited_league_id');
        });
    }
};
