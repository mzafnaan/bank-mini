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
                'email' => 'admin@bankmini.test',
                'email_verified_at' => now(),
                'password' => Hash::make('admin1234567'),
                'role' => 'administrator',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Teller Satu',
                'username' => 'teller1',
                'email' => 'teller1@bankmini.test',
                'email_verified_at' => now(),
                'password' => Hash::make('teller1234'),
                'role' => 'teller',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Hendra Wijaya, S.E.',
                'username' => 'supervisor',
                'email' => 'supervisor@bankmini.test',
                'email_verified_at' => now(),
                'password' => Hash::make('supervisor1234567'),
                'role' => 'supervisor',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Budi Santoso (Teller 2)',
                'username' => 'teller2',
                'email' => 'teller2@bankmini.test',
                'email_verified_at' => now(),
                'password' => Hash::make('teller1234'),
                'role' => 'teller',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
