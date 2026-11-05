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
  final TextEditingController _searchController = TextEditingController();
  String _selectedFilter = 'all'; // 'all', 'setoran', 'penarikan'
  DateTime? _selectedDate;

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
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? now,
      firstDate: DateTime(2020),
      lastDate: now,
      helpText: 'PILIH TANGGAL TRANSAKSI',
      cancelText: 'BATAL',
      confirmText: 'PILIH',
    );

    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  @override
  Widget build(BuildContext context) {
    final accountProvider = context.watch<AccountProvider>();
    final allTransactions = accountProvider.transactions;
    final isLoading = accountProvider.isHistoryLoading && allTransactions.isEmpty;

    final query = _searchController.text.trim().toLowerCase();
    final rawNumQuery = query.replaceAll(RegExp(r'[^0-9]'), '');

    final filteredTransactions = allTransactions.where((t) {
      // 1. Filter tipe transaksi
      if (_selectedFilter == 'setoran' && !t.isCredit) return false;
      if (_selectedFilter == 'penarikan' && t.isCredit) return false;

      // 2. Filter kalender tanggal
      if (_selectedDate != null) {
        final matchesDate = t.createdAt.year == _selectedDate!.year &&
            t.createdAt.month == _selectedDate!.month &&
            t.createdAt.day == _selectedDate!.day;
        if (!matchesDate) return false;
      }

      // 3. Filter query pencarian (Nominal atau Tanggal atau Kode)
      if (query.isNotEmpty) {
        // Cek nominal angka
        final amountIntStr = t.amount.toInt().toString();
        final formattedAmount = Formatters.currency(t.amount).toLowerCase();
        final bool matchesAmount = (rawNumQuery.isNotEmpty && amountIntStr.contains(rawNumQuery)) ||
            formattedAmount.contains(query);

        // Cek teks tanggal
        final dateStr = Formatters.dateTime(t.createdAt).toLowerCase();
        final dateFullStr = Formatters.dateTimeFull(t.createdAt).toLowerCase();
        final numericDateStr =
            '${t.createdAt.day.toString().padLeft(2, '0')}/${t.createdAt.month.toString().padLeft(2, '0')}/${t.createdAt.year}';
        final bool matchesDateText = dateStr.contains(query) ||
            dateFullStr.contains(query) ||
            numericDateStr.contains(query);

        final bool matchesCode = t.transactionCode.toLowerCase().contains(query);
        final bool matchesType = t.typeLabel.toLowerCase().contains(query);

        if (!matchesAmount && !matchesDateText && !matchesCode && !matchesType) {
          return false;
        }
      }

      return true;
    }).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F6),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title Header & Search
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Riwayat',
                    style: TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF111827),
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Search Bar for Date & Nominal
                  Container(
                    height: 46,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE5E7EB)),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.03),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: TextField(
                      controller: _searchController,
                      onChanged: (_) => setState(() {}),
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF111827),
                      ),
                      decoration: InputDecoration(
                        hintText: 'Cari nominal atau tanggal...',
                        hintStyle: const TextStyle(
                          fontSize: 13,
                          color: Color(0xFF9CA3AF),
                        ),
                        prefixIcon: const Icon(
                          Icons.search_rounded,
                          size: 20,
                          color: Color(0xFF6B7280),
                        ),
                        suffixIcon: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            if (_searchController.text.isNotEmpty)
                              IconButton(
                                icon: const Icon(Icons.close_rounded, size: 18, color: Color(0xFF6B7280)),
                                onPressed: () {
                                  _searchController.clear();
                                  setState(() {});
                                },
                              ),
                            IconButton(
                              icon: Icon(
                                _selectedDate != null
                                    ? Icons.event_available_rounded
                                    : Icons.calendar_month_outlined,
                                size: 20,
                                color: _selectedDate != null
                                    ? const Color(0xFF2563EB)
                                    : const Color(0xFF6B7280),
                              ),
                              onPressed: _pickDate,
                              tooltip: 'Filter Tanggal',
                            ),
                          ],
                        ),
                        border: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                    ),
                  ),

                  // Selected Date Chip (if picked from calendar)
                  if (_selectedDate != null) ...[
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEFF6FF),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFFBFDBFE)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.event, size: 14, color: Color(0xFF1D4ED8)),
                              const SizedBox(width: 4),
                              Text(
                                '${_selectedDate!.day.toString().padLeft(2, '0')}/${_selectedDate!.month.toString().padLeft(2, '0')}/${_selectedDate!.year}',
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF1D4ED8),
                                ),
                              ),
                              const SizedBox(width: 4),
                              GestureDetector(
                                onTap: () => setState(() => _selectedDate = null),
                                child: const Icon(Icons.close, size: 14, color: Color(0xFF1D4ED8)),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],

                  const SizedBox(height: 12),

                  // Segmented Filter Control matching design
                  Container(
                    width: double.infinity,
                    height: 44,
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE5E7EB),
                      borderRadius: BorderRadius.circular(12),
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
                                        Text(
                                          (query.isNotEmpty || _selectedDate != null)
                                              ? 'Transaksi Tidak Ditemukan'
                                              : 'Belum Ada Transaksi',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            fontWeight: FontWeight.w700,
                                            color: Color(0xFF111827),
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          (query.isNotEmpty || _selectedDate != null)
                                              ? 'Tidak ada transaksi yang sesuai dengan filter tanggal atau nominal.'
                                              : 'Transaksi tabungan akan muncul di sini.',
                                          textAlign: TextAlign.center,
                                          style: const TextStyle(
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
