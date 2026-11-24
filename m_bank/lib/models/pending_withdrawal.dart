/// Model representing a pending cash withdrawal authorization request.
class PendingWithdrawal {
  final int id;
  final double amount;
  final String tellerName;
  final String status;
  final DateTime? expiresAt;
  final int secondsRemaining;
  final DateTime? createdAt;

  PendingWithdrawal({
    required this.id,
    required this.amount,
    required this.tellerName,
    required this.status,
    this.expiresAt,
    required this.secondsRemaining,
    this.createdAt,
  });

  factory PendingWithdrawal.fromJson(Map<String, dynamic> json) {
    return PendingWithdrawal(
      id: (json['id'] as num?)?.toInt() ?? 0,
      amount: (json['amount'] as num?)?.toDouble() ?? 0,
      tellerName: json['teller_name'] as String? ?? 'Teller Bank',
      status: json['status'] as String? ?? 'waiting',
      expiresAt: json['expires_at'] != null
          ? DateTime.tryParse(json['expires_at'] as String)
          : null,
      secondsRemaining: (json['seconds_remaining'] as num?)?.toInt() ?? 0,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'] as String)
          : null,
    );
  }
}

