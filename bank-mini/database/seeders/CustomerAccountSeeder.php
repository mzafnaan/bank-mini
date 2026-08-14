<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAccountSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('customer_accounts')->insert([
            [
                'id' => 1,
                'customer_id' => 1,
                'username' => 'ahmad2024',
                'password' => Hash::make('password'),
                'pin' => Hash::make('123456'),
                'first_login' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_id' => 2,
                'username' => 'siti2024',
                'password' => Hash::make('password'),
                'pin' => Hash::make('123456'),
                'first_login' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'customer_id' => 3,
                'username' => 'budi2024',
                'password' => Hash::make('password'),
                'pin' => Hash::make('123456'),
                'first_login' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
