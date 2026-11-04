import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../../core/network/api_client.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../widgets/status_badge.dart';

class ManageEmailScreen extends StatefulWidget {
  const ManageEmailScreen({super.key});

  @override
  State<ManageEmailScreen> createState() => _ManageEmailScreenState();
}

class _ManageEmailScreenState extends State<ManageEmailScreen> {
  final _emailController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  final ApiClient _api = ApiClient.instance;

  bool _isLoading = true;
  bool _isSubmitting = false;
  String? _currentEmail;
  bool _isVerified = false;
  DateTime? _verifiedAt;
  String? _pendingEmail;

  @override
  void initState() {
    super.initState();
    _fetchEmailStatus();
  }

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _fetchEmailStatus() async {
    setState(() => _isLoading = true);
    try {
      final res = await _api.getEmailStatus();
      final data = res.data['data'] as Map<String, dynamic>;

      if (mounted) {
        setState(() {
          _currentEmail = data['email'] as String?;
          _isVerified = data['is_verified'] as bool? ?? false;
          _pendingEmail = data['pending_email'] as String?;
          if (data['verified_at'] != null) {
            _verifiedAt = DateTime.tryParse(data['verified_at'] as String);
          }
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _submitNewEmail() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);
    try {
      final res = await _api.changeEmail(email: _emailController.text.trim());
      final msg = res.data['message'] as String? ?? 'Tautan verifikasi telah dikirim.';

      if (mounted) {
        _emailController.clear();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(msg),
            backgroundColor: AppColors.success,
          ),
        );
        _fetchEmailStatus();
      }
    } on DioException catch (e) {
      final msg = e.response?.data is Map
          ? (e.response!.data as Map)['message']?.toString()
          : 'Gagal mengirim tautan verifikasi.';
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(msg ?? 'Terjadi kesalahan.'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  Future<void> _resendVerification() async {
    setState(() => _isSubmitting = true);
    try {
      final res = await _api.resendEmailVerification();
      final msg = res.data['message'] as String? ?? 'Tautan verifikasi telah dikirimkan ulang.';
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(msg), backgroundColor: AppColors.success),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal mengirim ulang verifikasi.'),
            backgroundColor: AppColors.danger,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  Future<void> _cancelPendingEmail() async {
    try {
      await _api.cancelPendingEmail();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Permintaan perubahan email dibatalkan.'),
          ),
        );
        _fetchEmailStatus();
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF111827)),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Email Pemulihan',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w700,
            color: Color(0xFF111827),
          ),
        ),
        centerTitle: false,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Kelola email pemulihan untuk reset password, kata sandi login, dan PIN transaksi Anda.',
                    style: TextStyle(
                      fontSize: 13,
                      color: Color(0xFF6B7280),
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Active Email Card
                  Container(
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              width: 42,
                              height: 42,
                              decoration: BoxDecoration(
                                color: const Color(0xFFF3F4F6),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: const Icon(
                                Icons.mail_outline_rounded,
                                size: 20,
                                color: Color(0xFF374151),
                              ),
                            ),
                            const SizedBox(width: 12),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Email Pemulihan Akun',
                                    style: TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF111827),
                                    ),
                                  ),
                                  SizedBox(height: 3),
                                  Text(
                                    'Digunakan untuk menerima tautan atur ulang password jika Anda lupa kata sandi.',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Color(0xFF6B7280),
                                      height: 1.3,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            if (_isVerified)
                              const StatusBadge(
                                text: '✓ Terverifikasi',
                                isSuccess: true,
                              )
                            else
                              const StatusBadge(
                                text: 'Belum Terverifikasi',
                                isSuccess: false,
                              ),
                          ],
                        ),
                        const SizedBox(height: 16),

                        // Inner active email container
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFEDF2F7)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'EMAIL PEMULIHAN AKTIF SAAT INI',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  letterSpacing: 0.5,
                                  color: Color(0xFF6B7280),
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                _currentEmail ?? 'Belum ada email pemulihan',
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF111827),
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                _verifiedAt != null
                                    ? 'Diverifikasi pada ${Formatters.dateTimeFull(_verifiedAt!)}'
                                    : 'Silakan verifikasi email Anda',
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: Color(0xFF9CA3AF),
                                ),
                              ),
                              const SizedBox(height: 12),
                              Row(
                                children: [
                                  const Text(
                                    'Status',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Color(0xFF6B7280),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Align(
                                      alignment: Alignment.centerRight,
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(
                                            horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: Colors.white,
                                          borderRadius: BorderRadius.circular(6),
                                          border: Border.all(color: const Color(0xFFE5E7EB)),
                                        ),
                                        child: Text(
                                          _isVerified
                                              ? 'Siap untuk Lupa Password'
                                              : 'Menunggu Verifikasi',
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.w500,
                                            color: _isVerified
                                                ? const Color(0xFF374151)
                                                : AppColors.warning,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),

                        // If pending email exists
                        if (_pendingEmail != null) ...[
                          const SizedBox(height: 12),
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFFBEB),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: const Color(0xFFFDE68A)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    const Icon(Icons.info_outline,
                                        size: 16, color: Color(0xFFD97706)),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        'Verifikasi pending untuk $_pendingEmail',
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w600,
                                          color: Color(0xFF92400E),
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.end,
                                  children: [
                                    TextButton(
                                      onPressed: _cancelPendingEmail,
                                      child: const Text('Batalkan',
                                          style: TextStyle(
                                              fontSize: 12, color: AppColors.danger)),
                                    ),
                                    const SizedBox(width: 8),
                                    TextButton(
                                      onPressed: _isSubmitting ? null : _resendVerification,
                                      child: const Text('Kirim Ulang',
                                          style: TextStyle(
                                              fontSize: 12, color: Color(0xFF1E232E))),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Change Email Form Card
                  Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(18),
                      border: Border.all(color: const Color(0xFFE5E7EB), width: 1),
                    ),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Ganti dengan Alamat Email Baru',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF111827),
                            ),
                          ),
                          const SizedBox(height: 16),

                          TextFormField(
                            controller: _emailController,
                            keyboardType: TextInputType.emailAddress,
                            decoration: InputDecoration(
                              hintText: 'Contoh: nama.nasabah@gmail.com',
                              prefixIcon: const Icon(
                                Icons.mail_outline_rounded,
                                size: 20,
                                color: Color(0xFF6B7280),
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 16, vertical: 14),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(
                                    color: Color(0xFF1E232E), width: 1.5),
                              ),
                              fillColor: Colors.white,
                            ),
                            validator: (val) {
                              if (val == null || val.trim().isEmpty) {
                                return 'Alamat email wajib diisi';
                              }
                              if (!val.contains('@') || !val.contains('.')) {
                                return 'Format email tidak valid';
                              }
                              return null;
                            },
                          ),
                          const SizedBox(height: 16),

                          ElevatedButton(
                            onPressed: _isSubmitting ? null : _submitNewEmail,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF1E232E),
                              foregroundColor: Colors.white,
                              minimumSize: const Size(double.infinity, 48),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              elevation: 0,
                            ),
                            child: _isSubmitting
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      valueColor:
                                          AlwaysStoppedAnimation<Color>(Colors.white),
                                    ),
                                  )
                                : const Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(Icons.send_rounded, size: 16),
                                      SizedBox(width: 8),
                                      Text(
                                        'Kirim Tautan Verifikasi',
                                        style: TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                    ],
                                  ),
                          ),
                          const SizedBox(height: 14),

                          const Text(
                            'Kami akan mengirimkan email konfirmasi. Jika Anda mengganti email lama, email lama Anda tidak akan diganti sampai email baru berhasil diverifikasi.',
                            style: TextStyle(
                              fontSize: 12,
                              color: Color(0xFF6B7280),
                              height: 1.4,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }
}

