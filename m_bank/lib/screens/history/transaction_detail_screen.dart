import 'package:flutter/material.dart';
import '../../core/utils/formatters.dart';
import '../../models/transaction.dart';

class TransactionDetailScreen extends StatelessWidget {
  final Transaction transaction;

  const TransactionDetailScreen({
    super.key,
    required this.transaction,
  });

  @override
  Widget build(BuildContext context) {
    final isCredit = transaction.isCredit;
    final heroBgColor = isCredit ? const Color(0xFFF0FDF4) : const Color(0xFFFEF2F2);
    final badgeBgColor = isCredit ? const Color(0xFFDCFCE7) : const Color(0xFFFEE2E2);
    final primaryColor = isCredit ? const Color(0xFF16A34A) : const Color(0xFFDC2626);
    final icon = isCredit ? Icons.arrow_downward_rounded : Icons.arrow_upward_rounded;

    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF111827)),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Detail Transaksi',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111827),
          ),
        ),
        centerTitle: false,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        child: Column(
          children: [
            // Top Hero Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 20),
              decoration: BoxDecoration(
                color: heroBgColor,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(
                  color: isCredit ? const Color(0xFFDCFCE7) : const Color(0xFFFEE2E2),
                ),
              ),
              child: Column(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: badgeBgColor,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(icon, color: primaryColor, size: 26),
                  ),
                  const SizedBox(height: 14),
                  Text(
                    transaction.typeLabel,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF4B5563),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    Formatters.signedCurrency(transaction.amount, isCredit: isCredit),
                    style: TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.w800,
                      color: primaryColor,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    transaction.transactionCode.isNotEmpty
                        ? transaction.transactionCode
                        : 'TRX-${transaction.id.toString().padLeft(5, '0')}',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: Color(0xFF6B7280),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // Detail Table Card
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              child: Column(
                children: [
                  _buildDetailRow(
                    label: 'Tanggal & Waktu',
                    value: Formatters.dateTime(transaction.createdAt),
                  ),
                  const Divider(color: Color(0xFFF3F4F6), height: 28),
                  _buildDetailRow(
                    label: 'No. Rekening',
                    value: transaction.accountNumber ?? '-',
                    valueStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF111827),
                    ),
                  ),
                  const Divider(color: Color(0xFFF3F4F6), height: 28),
                  _buildDetailRow(
                    label: 'Nama Nasabah',
                    value: transaction.customerName ?? '-',
                    valueStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF111827),
                    ),
                  ),
                  const Divider(color: Color(0xFFF3F4F6), height: 28),
                  _buildDetailRow(
                    label: 'Saldo Sebelum',
                    value: Formatters.currency(transaction.balanceBefore),
                  ),
                  const Divider(color: Color(0xFFF3F4F6), height: 28),
                  _buildDetailRow(
                    label: 'Nominal',
                    value: Formatters.currency(transaction.amount),
                    valueStyle: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: primaryColor,
                    ),
                  ),
                  const Divider(color: Color(0xFFF3F4F6), height: 28),
                  _buildDetailRow(
                    label: 'Saldo Sesudah',
                    value: Formatters.currency(transaction.balanceAfter),
                    valueStyle: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF111827),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Cetak Struk Button
            Container(
              width: double.infinity,
              height: 48,
              decoration: BoxDecoration(
                color: const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Fitur cetak struk belum diaktifkan.'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                  },
                  borderRadius: BorderRadius.circular(14),
                  child: const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.print_outlined, size: 20, color: Color(0xFF111827)),
                      SizedBox(width: 8),
                      Text(
                        'Cetak Struk',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF111827),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow({
    required String label,
    required String value,
    TextStyle? valueStyle,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            color: Color(0xFF6B7280),
          ),
        ),
        Text(
          value,
          style: valueStyle ??
              const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
                color: Color(0xFF111827),
              ),
        ),
      ],
    );
  }
}
