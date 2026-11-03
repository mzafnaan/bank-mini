import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../core/utils/formatters.dart';
import '../../models/transaction.dart';
import '../../providers/account_provider.dart';
import '../../widgets/transaction_item.dart';
import 'transaction_detail_screen.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen> {
  final ScrollController _scrollController = ScrollController();
  String _selectedFilter = 'all'; // 'all', 'setoran', 'penarikan'

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AccountProvider>().fetchTransactionHistory();
    });

    _scrollController.addListener(() {
      if (_scrollController.position.pixels >=
          _scrollController.position.maxScrollExtent - 200) {
        context.read<AccountProvider>().loadMoreTransactions();
      }
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final accountProvider = context.watch<AccountProvider>();
    final allTransactions = accountProvider.transactions;
    final isLoading = accountProvider.isHistoryLoading && allTransactions.isEmpty;

    final filteredTransactions = allTransactions.where((t) {
      if (_selectedFilter == 'setoran') return t.isCredit;
      if (_selectedFilter == 'penarikan') return !t.isCredit;
      return true;
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F6),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title Header
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 24, 20, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Riwayat',
                    style: TextStyle(
                      fontSize: 30,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF111827),
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Segmented Filter Control matching design
                  Container(
                    width: double.infinity,
                    height: 48,
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE5E7EB),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: Row(
                      children: [
                        _buildSegmentTab('Semua', 'all'),
                        _buildSegmentTab('Setoran', 'setoran'),
                        _buildSegmentTab('Penarikan', 'penarikan'),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Content List
            Expanded(
              child: isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : RefreshIndicator(
                      onRefresh: () =>
                          context.read<AccountProvider>().fetchTransactionHistory(),
                      child: filteredTransactions.isEmpty
                          ? ListView(
                              children: [
                                SizedBox(
                                  height: MediaQuery.of(context).size.height * 0.45,
                                  child: Center(
                                    child: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          width: 64,
                                          height: 64,
                                          decoration: BoxDecoration(
                                            color: Colors.white,
                                            borderRadius: BorderRadius.circular(20),
                                            border: Border.all(
                                                color: const Color(0xFFE5E7EB)),
                                          ),
                                          child: const Icon(
                                            Icons.receipt_long_rounded,
                                            size: 30,
                                            color: Color(0xFF9CA3AF),
                                          ),
                                        ),
                                        const SizedBox(height: 16),
                                        const Text(
                                          'Belum Ada Transaksi',
                                          style: TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.w700,
                                            color: Color(0xFF111827),
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        const Text(
                                          'Transaksi tabungan akan muncul di sini.',
                                          textAlign: TextAlign.center,
                                          style: TextStyle(
                                            fontSize: 13,
                                            color: Color(0xFF6B7280),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            )
                          : _buildGroupedTransactionList(
                              filteredTransactions,
                              accountProvider.hasMoreTransactions,
                            ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSegmentTab(String label, String value) {
    final isSelected = _selectedFilter == value;

    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _selectedFilter = value),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: isSelected ? Colors.white : Colors.transparent,
            borderRadius: BorderRadius.circular(10),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ]
                : null,
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 14,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
              color: isSelected
                  ? const Color(0xFF111827)
                  : const Color(0xFF4B5563),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildGroupedTransactionList(
    List<Transaction> items,
    bool hasMore,
  ) {
    // Group transactions by Month Year (e.g., "Oktober 2026")
    final Map<String, List<Transaction>> grouped = {};
    for (final trx in items) {
      final key = Formatters.monthYear(trx.createdAt);
      grouped.putIfAbsent(key, () => []).add(trx);
    }

    final groups = grouped.entries.toList();

    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.symmetric(horizontal: 20),
      itemCount: groups.length + (hasMore ? 1 : 0),
      itemBuilder: (context, groupIndex) {
        if (groupIndex == groups.length) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 16),
            child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
          );
        }

        final entry = groups[groupIndex];
        final monthHeader = entry.key;
        final transactionsInMonth = entry.value;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.only(top: 12, bottom: 10),
              child: Text(
                monthHeader,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF6B7280),
                ),
              ),
            ),
            ...transactionsInMonth.map((trx) {
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
        );
      },
    );
  }
}
