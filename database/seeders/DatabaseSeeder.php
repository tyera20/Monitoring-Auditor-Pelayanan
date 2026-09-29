<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'username' => 'admin',
        ], [
            'name' => 'Administrator',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::query()->updateOrCreate([
            'username' => 'guest',
        ], [
            'name' => 'Guest User',
            'password' => 'password',
            'role' => 'guest',
        ]);
    }
}
