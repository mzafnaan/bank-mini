/// Represents a single transaction record.
class Transaction {
  final int id;
  final String transactionCode;
  final String type; // 'setoran' or 'penarikan'
  final double amount;
  final double balanceBefore;
  final double balanceAfter;
  final String? description;
  final String? accountNumber;
  final String? customerName;
  final DateTime createdAt;

  Transaction({
    required this.id,
    required this.transactionCode,
    required this.type,
    required this.amount,
    required this.balanceBefore,
    required this.balanceAfter,
    this.description,
    this.accountNumber,
    this.customerName,
    required this.createdAt,
  });

  /// Whether this transaction is a credit (deposit/setoran).
  bool get isCredit => type.toLowerCase() == 'setoran';

  /// Display label for the transaction type.
  String get typeLabel {
    switch (type.toLowerCase()) {
      case 'setoran':
        return 'Setoran';
      case 'penarikan':
        return 'Penarikan';
      default:
        return type;
    }
  }

  factory Transaction.fromJson(Map<String, dynamic> json) {
    return Transaction(
      id: json['id'] as int,
      transactionCode: json['transaction_code'] as String? ?? '',
      type: json['type'] as String? ?? '',
      amount: (json['amount'] as num?)?.toDouble() ?? 0,
      balanceBefore: (json['balance_before'] as num?)?.toDouble() ?? 0,
      balanceAfter: (json['balance_after'] as num?)?.toDouble() ?? 0,
      description: json['description'] as String?,
      accountNumber: json['account_number'] as String?,
      customerName: json['customer_name'] as String?,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : DateTime.now(),
    );
  }
}

/// Represents paginated transaction history response.
class TransactionHistory {
  final List<Transaction> transactions;
  final int currentPage;
  final int lastPage;
  final int total;

  TransactionHistory({
    required this.transactions,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  bool get hasMore => currentPage < lastPage;

  factory TransactionHistory.fromJson(Map<String, dynamic> json) {
    final data = json['data'] as List<dynamic>? ?? [];
    return TransactionHistory(
      transactions: data
          .map((e) => Transaction.fromJson(e as Map<String, dynamic>))
          .toList(),
      currentPage: json['current_page'] as int? ?? 1,
      lastPage: json['last_page'] as int? ?? 1,
      total: json['total'] as int? ?? 0,
    );
  }
}

