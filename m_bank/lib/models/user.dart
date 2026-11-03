import 'customer.dart';
export 'customer.dart';

/// Represents the authenticated user account (customer_account).
class User {
  final int id;
  final String username;
  final bool firstLogin;
  final String status;
  final Customer? customer;

  User({
    required this.id,
    required this.username,
    required this.firstLogin,
    required this.status,
    this.customer,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      username: json['username'] as String? ?? '',
      firstLogin: json['first_login'] is bool
          ? json['first_login'] as bool
          : (json['first_login'] == 1 || json['first_login'] == true),
      status: json['status'] as String? ?? 'active',
      customer: json['customer'] != null
          ? Customer.fromJson(json['customer'] as Map<String, dynamic>)
          : null,
    );
  }

  /// Convenience getter for customer name.
  String get name => customer?.name ?? username;

  /// Convenience getter for account number.
  String get accountNumber => customer?.bankAccount?.accountNumber ?? '-';

  /// Convenience getter for balance.
  double get balance => customer?.bankAccount?.balance ?? 0;
}
