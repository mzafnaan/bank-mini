<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('academic_years')->updateOrInsert(
            ['name' => '2025/2026'],
            [
                'id' => 1,
                'start_date' => '2025-07-14',
                'end_date' => '2026-06-19',
                'is_active' => false,
                'created_at' => Carbon::parse('2025-07-01'),
                'updated_at' => Carbon::parse('2025-07-01'),
            ]
        );

        DB::table('academic_years')->updateOrInsert(
            ['name' => '2026/2027'],
            [
                'id' => 2,
                'start_date' => '2026-07-20',
                'end_date' => '2027-06-18',
                'is_active' => true,
                'created_at' => Carbon::parse('2026-07-01'),
                'updated_at' => Carbon::parse('2026-07-01'),
            ]
        );
    }
}

