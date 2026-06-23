<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@cyclingfantasy.com')->exists()) {
            return;
        }

        User::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Admin',
            'email' => 'admin@cyclingfantasy.com',
            'password' => bcrypt('admin1234'),
            'is_admin' => true,
        ]);
    }
}
