<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat Database VIEW menggunakan INNER JOIN multi-tabel (Standar Kompetensi UKK RPL).
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared("
            CREATE OR REPLACE VIEW `view_laporan_transaksi` AS
            SELECT 
                t.id AS transaction_id,
                t.type AS transaction_type,
                t.amount AS transaction_amount,
                t.created_at AS transaction_date,
                b.account_number,
                b.balance AS current_balance,
                c.nis,
                c.name AS customer_name,
                c.class_group,
                u.name AS teller_name
            FROM transactions t
            INNER JOIN bank_accounts b ON t.bank_account_id = b.id
            INNER JOIN customers c ON b.customer_id = c.id
            INNER JOIN users u ON t.teller_id = u.id;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared("DROP VIEW IF EXISTS `view_laporan_transaksi`;");
    }
};
