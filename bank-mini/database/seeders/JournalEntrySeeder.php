<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('journal_entries')->insert([
            [
                'id' => 1,
                'transaction_id' => 1,
                'account_code' => '101',
                'type' => 'debit',
                'amount' => 500000,
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'transaction_id' => 1,
                'account_code' => '201',
                'type' => 'credit',
                'amount' => 500000,
                'created_at' => now(),
            ],
        ]);
    }
}
