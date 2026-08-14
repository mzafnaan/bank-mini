<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WithdrawalRequestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('withdrawal_requests')->insert([
            [
                'id' => 1,
                'bank_account_id' => 1,
                'teller_id' => 2,
                'amount' => 100000,
                'status' => 'approved',
                'expires_at' => now()->addMinutes(5),
                'approved_at' => now(),
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'bank_account_id' => 2,
                'teller_id' => 3,
                'amount' => 50000,
                'status' => 'approved',
                'expires_at' => now()->addMinutes(5),
                'approved_at' => now(),
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'bank_account_id' => 3,
                'teller_id' => 2,
                'amount' => 25000,
                'status' => 'waiting',
                'expires_at' => now()->addMinutes(5),
                'approved_at' => null,
                'created_at' => now(),
            ],
            [
                'id' => 4,
                'bank_account_id' => 4,
                'teller_id' => 3,
                'amount' => 200000,
                'status' => 'expired',
                'expires_at' => now()->subHour(),
                'approved_at' => null,
                'created_at' => now(),
            ],
        ]);
    }
}
