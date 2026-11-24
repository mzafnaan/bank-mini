import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/constants/app_constants.dart';
import '../../core/utils/formatters.dart';
import '../../providers/account_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/pending_deactivation_card.dart';
import '../../widgets/pending_withdrawal_card.dart';
import '../../widgets/transaction_item.dart';
import '../history/transaction_detail_screen.dart';

class HomeScreen extends StatefulWidget {
  final VoidCallback? onViewAllTransactions;

  const HomeScreen({
    super.key,
    this.onViewAllTransactions,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _refreshData();
    });
  }

  Future<void> _refreshData() async {
    final accountProvider = context.read<AccountProvider>();
    final authProvider = context.read<AuthProvider>();

    await Future.wait([
      accountProvider.fetchDashboard(),
      authProvider.refreshProfile(),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final account = context.watch<AccountProvider>();
    final user = auth.user;
    final dashboard = account.dashboardData;

    final customerName = dashboard?.customerName.isNotEmpty == true
        ? dashboard!.customerName
        : (user?.name ?? 'Nasabah');

    final accountNumber = dashboard?.accountNumber.isNotEmpty == true
        ? dashboard!.accountNumber
        : (user?.accountNumber ?? '-');

    final balance = dashboard?.balance ?? user?.balance ?? 0;
    final totalDeposit = dashboard?.totalDeposit ?? 0;
    final totalWithdrawal = dashboard?.totalWithdrawal ?? 0;
    final recentTransactions = dashboard?.recentTransactions ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _refreshData,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top App Header
                Row(
                  children: [
                    Image.asset(
                      'assets/images/logo.png',
                      width: 42,
                      height: 42,
                      fit: BoxFit.contain,
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      AppConstants.appName,
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF111827),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 22),

                // Greeting
                const Text(
                  'Selamat datang kembali,',
                  style: TextStyle(
                    fontSize: 14,
                    color: Color(0xFF6B7280),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  customerName,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF111827),
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 20),

                // Pending Deactivation Request Alert Card
                if (dashboard?.pendingDeactivation != null &&
                    dashboard!.pendingDeactivation!.isPending)
                  PendingDeactivationCard(
                    request: dashboard.pendingDeactivation!,
                  ),

                // Pending Cash Withdrawal Alert Card
                if (account.pendingWithdrawal != null)
                  PendingWithdrawalCard(request: account.pendingWithdrawal!),

                // Main Saldo Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(22),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F3F5),
                    borderRadius: BorderRadius.circular(22),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Saldo Tabungan',
                            style: TextStyle(
                              fontSize: 13,
                              color: Color(0xFF6B7280),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            Formatters.currency(balance),
                            style: const TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF111827),
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'No. Rekening: $accountNumber',
                            style: const TextStyle(
                              fontSize: 13,
                              color: Color(0xFF6B7280),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                      Container(
                        width: 52,
                        height: 52,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.04),
                              blurRadius: 10,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: const Icon(
                          Icons.account_balance_wallet_outlined,
                          size: 26,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),

                // Summary Total Setoran & Penarikan Row
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Total Setoran',
                              style: TextStyle(
                                fontSize: 13,
                                color: Color(0xFF6B7280),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              Formatters.currency(totalDeposit),
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF111827),
                              ),
                            ),
                          ],
                        ),
                      ),
                      Container(
                        width: 1,
                        height: 38,
                        color: const Color(0xFFE5E7EB),
                      ),
                      const SizedBox(width: 20),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Total Penarikan',
                              style: TextStyle(
                                fontSize: 13,
                                color: Color(0xFF6B7280),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              Formatters.currency(totalWithdrawal),
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF111827),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 28),

                // Transaksi Terakhir Section
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Transaksi Terakhir',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF111827),
                      ),
                    ),
                    GestureDetector(
                      onTap: widget.onViewAllTransactions,
                      child: const Row(
                        children: [
                          Text(
                            'Lihat Semua',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w500,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                          SizedBox(width: 2),
                          Icon(
                            Icons.chevron_right_rounded,
                            size: 18,
                            color: Color(0xFF6B7280),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),

                // Recent Transactions List
                if (recentTransactions.isEmpty) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 36),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFE5E7EB)),
                    ),
                    child: const Center(
                      child: Column(
                        children: [
                          Icon(
                            Icons.receipt_long_outlined,
                            size: 36,
                            color: Color(0xFF9CA3AF),
                          ),
                          SizedBox(height: 10),
                          Text(
                            'Belum ada transaksi',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ] else ...[
                  ...recentTransactions.map((trx) {
                    return TransactionItem(
                      transaction: trx,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => TransactionDetailScreen(
                              transaction: trx,
                            ),
                          ),
                        );
                      },
                    );
                  }),
                ],
                const SizedBox(height: 20),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
