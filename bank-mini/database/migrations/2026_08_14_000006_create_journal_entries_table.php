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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')
                  ->constrained('transactions', 'id', 'fk_journal_entries_transaction')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->string('account_code', 10)->comment('101=Cash, 201=Saving');
            $table->enum('type', ['debit', 'credit']);
            $table->bigInteger('amount');
            $table->timestamp('created_at')->useCurrent();

            $table->index('transaction_id', 'idx_journal_entries_transaction');
            $table->index('account_code', 'idx_journal_entries_account_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
