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
        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')
                  ->unique('uq_customer_accounts_customer_id')
                  ->constrained('customers', 'id', 'fk_customer_accounts_customer')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
            $table->string('username', 50)->unique('uq_customer_accounts_username');
            $table->string('password');
            $table->string('pin')->comment('Stored hashed');
            $table->boolean('first_login')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_accounts');
    }
};
