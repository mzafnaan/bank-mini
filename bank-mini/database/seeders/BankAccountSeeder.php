<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankAccountSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('bank_accounts')->insert([
            [
                'id' => 1,
                'customer_id' => 1,
                'account_number' => 'DKV-001',
                'balance' => 562000,
                'status' => 'active',
                'qr_code' => 'DKV-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_id' => 2,
                'account_number' => 'RPL-002',
                'balance' => 300000,
                'status' => 'active',
                'qr_code' => 'RPL-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'customer_id' => 3,
                'account_number' => 'RPL-003',
                'balance' => 150000,
                'status' => 'active',
                'qr_code' => 'RPL-003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'customer_id' => 4,
                'account_number' => 'TKJ-004',
                'balance' => 500000,
                'status' => 'active',
                'qr_code' => 'TKJ-004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
