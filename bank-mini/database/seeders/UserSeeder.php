<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Administrator System',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'administrator',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Teller Satu',
                'username' => 'teller1',
                'password' => Hash::make('password'),
                'role' => 'teller',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Teller Dua',
                'username' => 'teller2',
                'password' => Hash::make('password'),
                'role' => 'teller',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Supervisor Bank',
                'username' => 'supervisor',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
