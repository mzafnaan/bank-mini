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
                'account_number' => 'RPL-001',
                'balance' => 250000,
                'status' => 'active',
                'qr_code' => 'RPL-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
