class StatsBreakdownItem {
  final String type;
  final String name;
  final double amount;
  final double percentage;
  final String colorHex;

  StatsBreakdownItem({
    required this.type,
    required this.name,
    required this.amount,
    required this.percentage,
    required this.colorHex,
  });

  factory StatsBreakdownItem.fromJson(Map<String, dynamic> json) {
    return StatsBreakdownItem(
      type: json['type'] as String? ?? 'deposit',
      name: json['name'] as String? ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      percentage: (json['percentage'] as num?)?.toDouble() ?? 0.0,
      colorHex: json['color'] as String? ?? '#22C55E',
    );
  }
}

class StatsTransactionItem {
  final int id;
  final String code;
  final String type; // 'deposit' or 'withdrawal'
  final double amount;
  final DateTime createdAt;

  StatsTransactionItem({
    required this.id,
    required this.code,
    required this.type,
    required this.amount,
    required this.createdAt,
  });

  bool get isDeposit => type == 'deposit';

  factory StatsTransactionItem.fromJson(Map<String, dynamic> json) {
    return StatsTransactionItem(
      id: json['id'] as int? ?? 0,
      code: json['code'] as String? ?? '',
      type: json['type'] as String? ?? 'deposit',
      amount: (json['amount'] as num?)?.toDouble() ?? 0.0,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
    );
  }
}

class MonthlyStatsData {
  final String period;
  final String periodLabel;
  final double currentBalance;
  final double totalIncome;
  final double totalExpense;
  final double netCashflow;
  final double depositPercentage;
  final double withdrawalPercentage;
  final int transactionCount;
  final List<StatsBreakdownItem> breakdown;
  final List<StatsTransactionItem> transactions;

  MonthlyStatsData({
    required this.period,
    required this.periodLabel,
    required this.currentBalance,
    required this.totalIncome,
    required this.totalExpense,
    required this.netCashflow,
    required this.depositPercentage,
    required this.withdrawalPercentage,
    required this.transactionCount,
    required this.breakdown,
    required this.transactions,
  });

  factory MonthlyStatsData.fromJson(Map<String, dynamic> json) {
    final breakdownList = json['breakdown'] as List<dynamic>? ?? [];
    final trxsList = json['transactions'] as List<dynamic>? ?? [];

    return MonthlyStatsData(
      period: json['period'] as String? ?? '',
      periodLabel: json['period_label'] as String? ?? '',
      currentBalance: (json['current_balance'] as num?)?.toDouble() ?? 0.0,
      totalIncome: (json['total_income'] as num?)?.toDouble() ?? 0.0,
      totalExpense: (json['total_expense'] as num?)?.toDouble() ?? 0.0,
      netCashflow: (json['net_cashflow'] as num?)?.toDouble() ?? 0.0,
      depositPercentage:
          (json['deposit_percentage'] as num?)?.toDouble() ?? 0.0,
      withdrawalPercentage:
          (json['withdrawal_percentage'] as num?)?.toDouble() ?? 0.0,
      transactionCount: json['transaction_count'] as int? ?? 0,
      breakdown: breakdownList
          .map((e) => StatsBreakdownItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      transactions: trxsList
          .map((e) => StatsTransactionItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

