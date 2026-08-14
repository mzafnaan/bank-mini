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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                  ->unique('uq_bank_accounts_customer_id')
                  ->constrained('customers', 'id', 'fk_bank_accounts_customer')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->string('account_number', 30)->unique('uq_bank_accounts_account_number');
            $table->bigInteger('balance')->default(0);
            $table->text('qr_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
