import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../constants/app_constants.dart';

/// Singleton API client using Dio with automatic token injection.
class ApiClient {
  static ApiClient? _instance;
  late final Dio _dio;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  ApiClient._() {
    _dio = Dio(
      BaseOptions(
        baseUrl: '${AppConstants.baseUrl}${AppConstants.apiPrefix}',
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      ),
    );

    // Request interceptor: attach Bearer token if available.
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _storage.read(key: AppConstants.tokenKey);
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (error, handler) {
          return handler.next(error);
        },
      ),
    );
  }

  /// Get the singleton instance.
  static ApiClient get instance {
    _instance ??= ApiClient._();
    return _instance!;
  }

  /// Access the underlying Dio instance.
  Dio get dio => _dio;

  // ─── Auth Endpoints ───────────────────────────────────────────

  /// Login with account number and password.
  Future<Response> login({
    required String username,
    required String password,
  }) {
    return _dio.post('/auth/login', data: {
      'username': username,
      'password': password,
    });
  }

  /// Request password reset link to email.
  Future<Response> forgotPassword({required String email}) {
    return _dio.post('/auth/forgot-password', data: {
      'email': email,
    });
  }

  /// Get authenticated user profile.
  Future<Response> getProfile() {
    return _dio.get('/auth/me');
  }

  /// Logout (revoke token).
  Future<Response> logout() {
    return _dio.post('/auth/logout');
  }

  /// Change password.
  Future<Response> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) {
    return _dio.post('/auth/change-password', data: {
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': newPasswordConfirmation,
    });
  }

  /// Change transaction PIN.
  Future<Response> changePin({
    required String currentPin,
    required String newPin,
    required String newPinConfirmation,
  }) {
    return _dio.post('/auth/change-pin', data: {
      'current_pin': currentPin,
      'new_pin': newPin,
      'new_pin_confirmation': newPinConfirmation,
    });
  }

  // ─── Nasabah / Account Endpoints ──────────────────────────────

  /// Get account balance and summary.
  Future<Response> getDashboard() {
    return _dio.get('/nasabah/dashboard');
  }

  /// Get transaction history with pagination.
  Future<Response> getTransactionHistory({int page = 1}) {
    return _dio.get('/nasabah/riwayat', queryParameters: {'page': page});
  }

  /// Get transaction detail by ID.
  Future<Response> getTransactionDetail(int transactionId) {
    return _dio.get('/nasabah/riwayat/$transactionId');
  }

  /// Get account/rekening information.
  Future<Response> getAccountInfo() {
    return _dio.get('/nasabah/rekening');
  }

  /// Get pending cash withdrawal request for the customer.
  Future<Response> getPendingWithdrawal() {
    return _dio.get('/nasabah/withdrawal-requests/pending');
  }

  /// Authorize a pending withdrawal request with 6-digit PIN.
  Future<Response> authorizeWithdrawal({
    required int requestId,
    required String pin,
  }) {
    return _dio.post('/nasabah/withdrawal-requests/$requestId/authorize', data: {
      'pin': pin,
    });
  }

  /// Reject a pending withdrawal request.
  Future<Response> rejectWithdrawal(int requestId) {
    return _dio.post('/nasabah/withdrawal-requests/$requestId/reject');
  }

  /// Get recovery email status.
  Future<Response> getEmailStatus() {
    return _dio.get('/auth/email');
  }

  /// Change recovery email.
  Future<Response> changeEmail({required String email}) {
    return _dio.post('/auth/change-email', data: {
      'email': email,
    });
  }

  /// Resend verification email.
  Future<Response> resendEmailVerification() {
    return _dio.post('/auth/resend-email');
  }

  /// Cancel pending email change.
  Future<Response> cancelPendingEmail() {
    return _dio.post('/auth/cancel-pending-email');
  }

  // ─── Token Management ─────────────────────────────────────────

  /// Save access token to secure storage.
  Future<void> saveToken(String token) async {
    await _storage.write(key: AppConstants.tokenKey, value: token);
  }

  /// Remove access token from secure storage.
  Future<void> removeToken() async {
    await _storage.delete(key: AppConstants.tokenKey);
  }

  /// Check if user has a stored token.
  Future<bool> hasToken() async {
    final token = await _storage.read(key: AppConstants.tokenKey);
    return token != null && token.isNotEmpty;
  }
}

