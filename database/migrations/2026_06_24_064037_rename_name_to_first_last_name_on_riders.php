<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('profile_image')->nullable()->after('birth_date');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'profile_image']);
        });

        Schema::table('riders', function (Blueprint $table) {
            $table->string('name')->after('id');
        });
    }
};
