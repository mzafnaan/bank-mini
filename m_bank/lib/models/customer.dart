/// Represents a customer (nasabah) profile.
class Customer {
  final int id;
  final String name;
  final String? nik;
  final String? address;
  final String? phone;
  final String? email;
  final BankAccount? bankAccount;

  Customer({
    required this.id,
    required this.name,
    this.nik,
    this.address,
    this.phone,
    this.email,
    this.bankAccount,
  });

  factory Customer.fromJson(Map<String, dynamic> json) {
    return Customer(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      nik: json['nik'] as String?,
      address: json['address'] as String?,
      phone: json['phone'] as String?,
      email: json['email'] as String?,
      bankAccount: json['bank_account'] != null
          ? BankAccount.fromJson(json['bank_account'] as Map<String, dynamic>)
          : null,
    );
  }
}

/// Represents a bank account (rekening).
class BankAccount {
  final int id;
  final String accountNumber;
  final double balance;
  final String status;
  final String? createdAt;

  BankAccount({
    required this.id,
    required this.accountNumber,
    required this.balance,
    required this.status,
    this.createdAt,
  });

  factory BankAccount.fromJson(Map<String, dynamic> json) {
    return BankAccount(
      id: json['id'] as int,
      accountNumber: json['account_number'] as String? ?? '',
      balance: (json['balance'] as num?)?.toDouble() ?? 0,
      status: json['status'] as String? ?? 'active',
      createdAt: json['created_at'] as String?,
    );
  }
}

