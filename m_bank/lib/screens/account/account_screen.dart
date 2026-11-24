import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:qr_flutter/qr_flutter.dart';
import '../../core/utils/formatters.dart';
import '../../providers/account_provider.dart';
import '../../providers/auth_provider.dart';

class AccountScreen extends StatefulWidget {
  const AccountScreen({super.key});

  @override
  State<AccountScreen> createState() => _AccountScreenState();
}

class _AccountScreenState extends State<AccountScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AccountProvider>().fetchAccountInfo();
    });
  }

  @override
  Widget build(BuildContext context) {
    final accountProvider = context.watch<AccountProvider>();
    final authProvider = context.watch<AuthProvider>();
    final info = accountProvider.accountInfo;
    final user = authProvider.user;

    final customer = info?['customer'] as Map<String, dynamic>? ?? {};
    final account = info?['account'] as Map<String, dynamic>? ?? {};

    final name = customer['name']?.toString() ?? user?.name ?? 'Nasabah';
    final accountNumber =
        account['account_number']?.toString() ?? user?.accountNumber ?? '-';
    final balance = (account['balance'] as num?)?.toDouble() ?? user?.balance ?? 0;

    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F6),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => context.read<AccountProvider>().fetchAccountInfo(),
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Title Header
                const Text(
                  'Rekening',
                  style: TextStyle(
                    fontSize: 30,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF111827),
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 20),

                // Top Card with QR Code
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
                  ),
                  child: Column(
                    children: [
                      // QR Code Container
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
                        ),
                        child: QrImageView(
                          data: accountNumber.isNotEmpty && accountNumber != '-'
                              ? accountNumber
                              : 'BANK-MINI-NASABAH',
                          version: QrVersions.auto,
                          size: 180,
                          eyeStyle: const QrEyeStyle(
                            eyeShape: QrEyeShape.square,
                            color: Color(0xFF111827),
                          ),
                          dataModuleStyle: const QrDataModuleStyle(
                            dataModuleShape: QrDataModuleShape.square,
                            color: Color(0xFF111827),
                          ),
                        ),
                      ),
                      const SizedBox(height: 18),

                      // Customer Name & Account Number
                      Text(
                        name,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF111827),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        accountNumber,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w500,
                          color: Color(0xFF6B7280),
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Instruction Subtitle
                      const Text(
                        'Tunjukkan kode ini kepada petugas\nBank Mini.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 13,
                          color: Color(0xFF6B7280),
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Details Card
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
                  ),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  child: Column(
                    children: [
                      _buildRow('Nama Nasabah', name),
                      const Divider(color: Color(0xFFF3F4F6), height: 26),
                      _buildRow('No. Rekening', accountNumber),
                      const Divider(color: Color(0xFFF3F4F6), height: 26),
                      _buildRow('Jenis Rekening', 'Tabungan'),
                      const Divider(color: Color(0xFFF3F4F6), height: 26),
                      _buildRow(
                        'Saldo',
                        Formatters.currency(balance),
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
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRow(String label, String value, {TextStyle? valueStyle}) {
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
