<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('nationality')->nullable();
            $table->date('birth_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
