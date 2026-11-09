<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('class_group', 50)->nullable()->after('class');
            $table->foreignId('academic_year_id')
                  ->nullable()
                  ->after('class_group')
                  ->constrained('academic_years')
                  ->nullOnDelete();
            $table->string('status', 20)->default('active')->after('pending_email');
        });

        // Safe data migration for existing customers:
        // If class contains composite string (e.g. "XII RPL 1"), split into class="XII" and class_group="RPL 1"
        $customers = DB::table('customers')->get();
        foreach ($customers as $customer) {
            $currentClass = trim((string) $customer->class);
            $newClass = $currentClass;
            $classGroup = null;

            if (preg_match('/^(X|XI|XII)\s+(.+)$/i', $currentClass, $matches)) {
                $newClass = strtoupper($matches[1]);
                $classGroup = trim($matches[2]);
            }

            DB::table('customers')->where('id', $customer->id)->update([
                'class' => $newClass,
                'class_group' => $classGroup,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn(['class_group', 'academic_year_id', 'status']);
        });
    }
};

