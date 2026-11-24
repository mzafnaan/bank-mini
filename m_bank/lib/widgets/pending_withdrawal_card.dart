import 'package:flutter/material.dart';
import '../core/utils/formatters.dart';
import '../models/pending_withdrawal.dart';
import 'withdrawal_pin_sheet.dart';

/// Prominent card shown on Home Screen when a cash withdrawal request is waiting for customer PIN.
class PendingWithdrawalCard extends StatelessWidget {
  final PendingWithdrawal request;

  const PendingWithdrawalCard({
    super.key,
    required this.request,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 18),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFFEF3C7), // Warm amber background
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFFCD34D), width: 1.5),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFFD97706).withValues(alpha: 0.12),
            blurRadius: 14,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row with pulsing badge
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    width: 32,
                    height: 32,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.shield_rounded,
                      color: Colors.white,
                      size: 18,
                    ),
                  ),
                  const SizedBox(width: 10),
                  const Text(
                    'Konfirmasi Penarikan',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF92400E),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFFDE68A),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    SizedBox(
                      width: 7,
                      height: 7,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: Color(0xFFD97706),
                        ),
                      ),
                    ),
                    SizedBox(width: 5),
                    Text(
                      'Perlu PIN',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF92400E),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Message & Details
          Text(
            'Teller ${request.tellerName} meminta otorisasi penarikan uang tunai sebesar:',
            style: const TextStyle(
              fontSize: 12,
              color: Color(0xFF78350F),
              height: 1.4,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            Formatters.currency(request.amount),
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.w900,
              color: Color(0xFF92400E),
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 14),

          // Action Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () => WithdrawalPinSheet.show(context, request),
              icon: const Icon(Icons.lock_open_rounded, size: 18),
              label: const Text(
                'Masukkan PIN Otorisasi',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF1E293B),
                foregroundColor: Colors.white,
                elevation: 0,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

