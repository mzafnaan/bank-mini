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
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teller_id')
                  ->constrained('users', 'id', 'fk_daily_reports_teller')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('supervisor_id')
                  ->nullable()
                  ->constrained('users', 'id', 'fk_daily_reports_supervisor')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->date('report_date');
            $table->bigInteger('opening_cash')->default(0);
            $table->bigInteger('total_deposit')->default(0);
            $table->bigInteger('total_withdrawal')->default(0);
            $table->bigInteger('closing_cash')->default(0);
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['teller_id', 'report_date'], 'uq_daily_reports_teller_date');
            $table->index('supervisor_id', 'idx_daily_reports_supervisor');
            $table->index('report_date', 'idx_daily_reports_report_date');
            $table->index('status', 'idx_daily_reports_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
