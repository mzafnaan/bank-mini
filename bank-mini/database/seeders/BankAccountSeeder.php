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
                'account_number' => 'REK-2024001',
                'balance' => 562000,
                'qr_code' => 'QR-REK-2024001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'customer_id' => 2,
                'account_number' => 'REK-2024002',
                'balance' => 300000,
                'qr_code' => 'QR-REK-2024002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'customer_id' => 3,
                'account_number' => 'REK-2024003',
                'balance' => 150000,
                'qr_code' => 'QR-REK-2024003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'customer_id' => 4,
                'account_number' => 'REK-2024004',
                'balance' => 500000,
                'qr_code' => 'QR-REK-2024004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
