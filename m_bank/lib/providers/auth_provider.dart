import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../core/constants/app_constants.dart';
import '../core/network/api_client.dart';
import '../models/user.dart';

/// Manages authentication state: login, logout, token persistence.
class AuthProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient.instance;
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  User? _user;
  bool _isLoading = false;
  bool _isInitializing = true;
  String? _error;

  User? get user => _user;
  bool get isLoading => _isLoading;
  bool get isInitializing => _isInitializing;
  bool get isAuthenticated => _user != null;
  String? get error => _error;

  /// Try to restore session from stored token on app launch.
  Future<void> initialize() async {
    _isInitializing = true;
    notifyListeners();

    try {
      final hasToken = await _api.hasToken();
      if (hasToken) {
        await _fetchProfile();
      }
    } catch (_) {
      // Token invalid or expired – clear it.
      await _api.removeToken();
    } finally {
      _isInitializing = false;
      notifyListeners();
    }
  }

  /// Login with account number (username) and password.
  Future<bool> login({
    required String username,
    required String password,
    bool rememberMe = false,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.login(
        username: username,
        password: password,
      );

      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        final loginData = data['data'] as Map<String, dynamic>;
        final token = loginData['access_token'] as String;
        await _api.saveToken(token);

        // Handle remember me
        if (rememberMe) {
          await _storage.write(
            key: AppConstants.rememberMeKey,
            value: 'true',
          );
          await _storage.write(
            key: AppConstants.savedUsernameKey,
            value: username,
          );
        } else {
          await _storage.delete(key: AppConstants.rememberMeKey);
          await _storage.delete(key: AppConstants.savedUsernameKey);
        }

        // Fetch full profile
        await _fetchProfile();

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _error = data['message'] as String? ?? 'Login gagal.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } on DioException catch (e) {
      if (e.response != null) {
        final data = e.response!.data;
        if (data is Map<String, dynamic>) {
          _error = data['message'] as String? ?? 'Login gagal.';
        } else {
          _error = 'Login gagal. Periksa koneksi Anda.';
        }
      } else {
        _error = 'Tidak dapat terhubung ke server.';
      }
      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Terjadi kesalahan. Silakan coba lagi.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  /// Logout and clear session.
  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _api.logout();
    } catch (_) {
      // Ignore logout API errors; still clear local state.
    }

    await _api.removeToken();
    _user = null;
    _isLoading = false;
    notifyListeners();
  }

  /// Fetch user profile from server.
  Future<void> _fetchProfile() async {
    final response = await _api.getProfile();
    final data = response.data as Map<String, dynamic>;

    if (data['success'] == true) {
      _user = User.fromJson(data['data'] as Map<String, dynamic>);
    }
  }

  /// Refresh the user profile.
  Future<void> refreshProfile() async {
    try {
      await _fetchProfile();
      notifyListeners();
    } catch (_) {
      // Silently fail on refresh.
    }
  }

  /// Check if "remember me" was saved.
  Future<String?> getSavedUsername() async {
    final remember = await _storage.read(key: AppConstants.rememberMeKey);
    if (remember == 'true') {
      return _storage.read(key: AppConstants.savedUsernameKey);
    }
    return null;
  }

  /// Check if remember me is enabled.
  Future<bool> isRememberMeEnabled() async {
    final remember = await _storage.read(key: AppConstants.rememberMeKey);
    return remember == 'true';
  }

  /// Send forgot password request via email.
  Future<Map<String, dynamic>> sendForgotPassword(String email) async {
    try {
      final response = await _api.forgotPassword(email: email);
      final data = response.data as Map<String, dynamic>;
      return {
        'success': data['success'] == true,
        'message': data['message'] as String? ?? 'Permintaan berhasil dikirim.',
      };
    } on DioException catch (e) {
      String msg = 'Gagal mengirim permintaan reset password.';
      if (e.response?.data is Map<String, dynamic>) {
        msg = e.response!.data['message'] as String? ?? msg;
      }
      return {
        'success': false,
        'message': msg,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Terjadi kesalahan sistem: $e',
      };
    }
  }

  /// Clear error message.
  void clearError() {
    _error = null;
    notifyListeners();
  }

  /// Reset PIN using account password verification (Lupa PIN).
  Future<Map<String, dynamic>> resetPinWithPassword({
    required String password,
    required String newPin,
    required String newPinConfirmation,
  }) async {
    try {
      final response = await _api.resetPinWithPassword(
        password: password,
        newPin: newPin,
        newPinConfirmation: newPinConfirmation,
      );
      final data = response.data as Map<String, dynamic>;
      return {
        'success': data['success'] == true,
        'message': data['message'] as String? ?? 'PIN berhasil diperbarui.',
      };
    } on DioException catch (e) {
      String msg = 'Gagal memperbarui PIN.';
      if (e.response?.data is Map<String, dynamic>) {
        msg = e.response!.data['message'] as String? ?? msg;
      }
      return {
        'success': false,
        'message': msg,
      };
    } catch (e) {
      return {
        'success': false,
        'message': 'Terjadi kesalahan sistem: $e',
      };
    }
  }
}

