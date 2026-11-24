import 'pending_withdrawal.dart';
import 'transaction.dart';

/// Customer deactivation approval request sent by Admin.
class PendingDeactivation {
  final bool isPending;
  final DateTime? requestedAt;
  final String reason;

  PendingDeactivation({
    required this.isPending,
    this.requestedAt,
    required this.reason,
  });

  factory PendingDeactivation.fromJson(Map<String, dynamic> json) {
    return PendingDeactivation(
      isPending: json['is_pending'] as bool? ?? false,
      requestedAt: json['requested_at'] != null
          ? DateTime.tryParse(json['requested_at'].toString())
          : null,
      reason: json['reason'] as String? ??
          'Administrator telah mengajukan penonaktifan rekening Anda.',
    );
  }
}

/// Dashboard summary data returned by the API.
class DashboardData {
  final String customerName;
  final String accountNumber;
  final double balance;
  final double totalDeposit;
  final double totalWithdrawal;
  final List<Transaction> recentTransactions;
  final PendingWithdrawal? pendingWithdrawal;
  final PendingDeactivation? pendingDeactivation;

  DashboardData({
    required this.customerName,
    required this.accountNumber,
    required this.balance,
    required this.totalDeposit,
    required this.totalWithdrawal,
    required this.recentTransactions,
    this.pendingWithdrawal,
    this.pendingDeactivation,
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
      pendingWithdrawal: json['pending_withdrawal'] != null
          ? PendingWithdrawal.fromJson(
              json['pending_withdrawal'] as Map<String, dynamic>,
            )
          : null,
      pendingDeactivation: json['pending_deactivation'] != null
          ? PendingDeactivation.fromJson(
              json['pending_deactivation'] as Map<String, dynamic>,
            )
          : null,
    );
  }
}
