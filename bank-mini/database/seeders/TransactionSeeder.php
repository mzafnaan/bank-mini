<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('transactions')->insert([
            [
                'id' => 1,
                'bank_account_id' => 1,
                'teller_id' => 2,
                'type' => 'deposit',
                'amount' => 250000,
                'created_at' => now(),
            ],
        ]);
    }
}
