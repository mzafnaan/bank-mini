import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../models/dashboard_data.dart';
import '../providers/account_provider.dart';
import '../providers/auth_provider.dart';

class PendingDeactivationCard extends StatefulWidget {
  final PendingDeactivation request;

  const PendingDeactivationCard({
    super.key,
    required this.request,
  });

  @override
  State<PendingDeactivationCard> createState() =>
      _PendingDeactivationCardState();
}

class _PendingDeactivationCardState extends State<PendingDeactivationCard> {
  bool _isLoading = false;

  Future<void> _handleApprove() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Color(0xFFDC2626), size: 26),
            SizedBox(width: 10),
            Text(
              'Setujui Penonaktifan?',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: const Text(
          'Rekening tabungan dan akun m-banking Anda akan segera dinonaktifkan setelah Anda menyetujui permohonan ini. Anda akan otomatis keluar dari aplikasi.',
          style: TextStyle(fontSize: 14, color: Color(0xFF4B5563)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal', style: TextStyle(color: Color(0xFF6B7280))),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFDC2626),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            child: const Text('Ya, Setujui'),
          ),
        ],
      ),
    );

    if (confirmed != true || !mounted) return;

    setState(() => _isLoading = true);

    final res = await context.read<AccountProvider>().approveDeactivation();
    if (!mounted) return;

    setState(() => _isLoading = false);

    if (res.success) {
      await context.read<AuthProvider>().logout();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: const Color(0xFF1F2937),
        ),
      );
      Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  Future<void> _handleReject() async {
    setState(() => _isLoading = true);

    final res = await context.read<AccountProvider>().rejectDeactivation();
    if (!mounted) return;

    setState(() => _isLoading = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(res.message),
        backgroundColor: res.success ? const Color(0xFF059669) : Colors.redAccent,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final requestedAtStr = widget.request.requestedAt != null
        ? DateFormat('dd MMM yyyy, HH:mm').format(widget.request.requestedAt!)
        : null;

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 18),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFFEF2F2), // Light red/coral alert background
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFFCA5A5),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.red.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: const Color(0xFFDC2626).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.shield_outlined,
                  color: Color(0xFFDC2626),
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Permintaan Penonaktifan Rekening',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF991B1B),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      requestedAtStr != null
                          ? 'Diajukan pada: $requestedAtStr'
                          : 'Diajukan oleh Administrator',
                      style: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFFB91C1C),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFFECACA)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Alasan / Catatan Admin:',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF6B7280),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  widget.request.reason,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF1F2937),
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          if (_isLoading)
            const Center(
              child: SizedBox(
                height: 24,
                width: 24,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: Color(0xFFDC2626),
                ),
              ),
            )
          else
            Row(
              children: [
                // Tombol Tolak
                Expanded(
                  child: OutlinedButton(
                    onPressed: _handleReject,
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFD1D5DB)),
                      foregroundColor: const Color(0xFF374151),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text(
                      'Tolak',
                      style: TextStyle(fontWeight: FontWeight.w700),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                // Tombol Setujui
                Expanded(
                  child: ElevatedButton(
                    onPressed: _handleApprove,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFDC2626),
                      foregroundColor: Colors.white,
                      elevation: 0,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text(
                      'Setujui Nonaktif',
                      style: TextStyle(fontWeight: FontWeight.w700),
                    ),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }
}

