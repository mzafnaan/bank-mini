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
                'nis' => '2024001',
                'name' => 'Ahmad Fauzi',
                'class' => 'XII DKV',
                'phone' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nis' => '2024002',
                'name' => 'Siti Aminah',
                'class' => 'XII RPL 1',
                'phone' => '081234567891',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nis' => '2024003',
                'name' => 'Budi Santoso',
                'class' => 'XII RPL 2',
                'phone' => '081234567892',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nis' => '2024004',
                'name' => 'Dewi Lestari',
                'class' => 'XI TKJ 1',
                'phone' => '081234567893',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
