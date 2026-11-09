<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('customers')->insert([
            [
                'id' => 1,
                'nis' => '17782',
                'name' => 'Muhammad Afnaan',
                'class' => 'XII RPL 1',
                'phone' => '081234567890',
                'email' => 'muhammadafnaan25@gmail.com',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
