<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS `add_balance`;
            CREATE PROCEDURE `add_balance` (IN `p_account_id` BIGINT, IN `p_amount` DECIMAL(15,2))
            BEGIN
                UPDATE bank_accounts
                SET balance = balance + p_amount
                WHERE id = p_account_id;
            END;

            DROP PROCEDURE IF EXISTS `count_customers`;
            CREATE PROCEDURE `count_customers` (OUT `p_total` INT)
            BEGIN
                SELECT COUNT(*)
                INTO p_total
                FROM customers;
            END;

            DROP PROCEDURE IF EXISTS `view_balance`;
            CREATE PROCEDURE `view_balance` (IN `p_account_id` INT, OUT `p_balance` DECIMAL(15,2))
            BEGIN
                SELECT balance
                INTO p_balance
                FROM bank_accounts
                WHERE id = p_account_id;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS `add_balance`;
            DROP PROCEDURE IF EXISTS `count_customers`;
            DROP PROCEDURE IF EXISTS `view_balance`;
        ");
    }
};
