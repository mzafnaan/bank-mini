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
                'username' => '17782',
                'password' => Hash::make('password'),
                'pin' => Hash::make('123456'),
                'first_login' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}