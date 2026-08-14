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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')
                  ->constrained('bank_accounts', 'id', 'fk_transactions_bank_account')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('teller_id')
                  ->constrained('users', 'id', 'fk_transactions_teller')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->bigInteger('amount');
            $table->timestamp('created_at')->useCurrent();

            $table->index('bank_account_id', 'idx_transactions_bank_account');
            $table->index('teller_id', 'idx_transactions_teller');
            $table->index('type', 'idx_transactions_type');
            $table->index('created_at', 'idx_transactions_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
