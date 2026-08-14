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
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')
                  ->constrained('bank_accounts', 'id', 'fk_withdrawal_requests_bank_account')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->foreignId('teller_id')
                  ->constrained('users', 'id', 'fk_withdrawal_requests_teller')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->bigInteger('amount');
            $table->enum('status', ['waiting', 'approved', 'rejected', 'expired'])->default('waiting');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('bank_account_id', 'idx_withdrawal_requests_bank_account');
            $table->index('teller_id', 'idx_withdrawal_requests_teller');
            $table->index('status', 'idx_withdrawal_requests_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
