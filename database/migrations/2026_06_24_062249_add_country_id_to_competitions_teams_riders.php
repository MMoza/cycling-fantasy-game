<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->char('country_id', 2)->nullable()->after('type');
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('country');
            $table->char('country_id', 2)->nullable()->after('name');
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn('nationality');
            $table->char('country_id', 2)->nullable()->after('name');
            $table->foreign('country_id')->references('id')->on('countries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('competitions', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->string('country')->nullable();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->string('country')->nullable();
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
            $table->string('nationality')->nullable();
        });
    }
};
