<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('competitions')->where('type', 'grand_tour')->update(['type' => 'gc']);
        DB::table('competitions')->where('type', 'week_tour')->update(['type' => 'major']);
        DB::table('competitions')->where('type', 'one_week')->update(['type' => 'major']);
    }

    public function down(): void
    {
        DB::table('competitions')->where('type', 'gc')->update(['type' => 'grand_tour']);
        DB::table('competitions')->where('type', 'major')->update(['type' => 'week_tour']);
    }
};
