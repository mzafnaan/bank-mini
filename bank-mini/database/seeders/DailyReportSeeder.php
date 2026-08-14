<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyReportSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('daily_reports')->insert([
            [
                'id' => 1,
                'teller_id' => 2,
                'supervisor_id' => 4,
                'report_date' => now()->toDateString(),
                'opening_cash' => 1000000,
                'total_deposit' => 900000,
                'total_withdrawal' => 0,
                'closing_cash' => 1900000,
                'status' => 'approved',
                'approved_at' => now(),
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'teller_id' => 3,
                'supervisor_id' => null,
                'report_date' => now()->toDateString(),
                'opening_cash' => 500000,
                'total_deposit' => 750000,
                'total_withdrawal' => 150000,
                'closing_cash' => 1100000,
                'status' => 'draft',
                'approved_at' => null,
                'created_at' => now(),
            ],
        ]);
    }
}
