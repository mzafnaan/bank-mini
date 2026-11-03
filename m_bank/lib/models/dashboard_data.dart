import 'transaction.dart';

/// Dashboard summary data returned by the API.
class DashboardData {
  final String customerName;
  final String accountNumber;
  final double balance;
  final double totalDeposit;
  final double totalWithdrawal;
  final List<Transaction> recentTransactions;

  DashboardData({
    required this.customerName,
    required this.accountNumber,
    required this.balance,
    required this.totalDeposit,
    required this.totalWithdrawal,
    required this.recentTransactions,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    final recent = json['recent_transactions'] as List<dynamic>? ?? [];
    return DashboardData(
      customerName: json['customer_name'] as String? ?? '',
      accountNumber: json['account_number'] as String? ?? '',
      balance: (json['balance'] as num?)?.toDouble() ?? 0,
      totalDeposit: (json['total_deposit'] as num?)?.toDouble() ?? 0,
      totalWithdrawal: (json['total_withdrawal'] as num?)?.toDouble() ?? 0,
      recentTransactions: recent
          .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}
