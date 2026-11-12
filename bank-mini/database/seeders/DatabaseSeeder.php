<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AcademicYearSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            BankAccountSeeder::class,
            CustomerAccountSeeder::class,
            TransactionSeeder::class,
            WithdrawalRequestSeeder::class,
            JournalEntrySeeder::class,
            DailyReportSeeder::class,
        ]);
    }
}
