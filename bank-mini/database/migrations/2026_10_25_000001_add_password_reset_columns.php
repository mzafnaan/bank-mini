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
        // Add email & email_verified_at to users table (for internal staff password reset)
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique('uq_users_email')->after('username');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Add email & email_verified_at to customers table (for customer password reset via email)
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->unique('uq_customers_email')->after('phone');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Separate password reset tokens table for customer accounts
        // This prevents cross-guard token usage between users and customer_accounts
        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['email', 'email_verified_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email', 'email_verified_at']);
        });
    }
};
