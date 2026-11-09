<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_class_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();
            $table->string('class', 20);
            $table->string('class_group', 50)->nullable();
            $table->timestamps();

            // Unique constraint on combination of customer_id and academic_year_id
            $table->unique(['customer_id', 'academic_year_id'], 'uq_cust_class_hist_cust_ay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_class_histories');
    }
};

