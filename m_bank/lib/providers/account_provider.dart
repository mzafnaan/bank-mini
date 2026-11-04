import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/network/api_client.dart';
import '../models/dashboard_data.dart';
import '../models/pending_withdrawal.dart';
import '../models/transaction.dart';

/// Manages dashboard data, transaction history, account info, and withdrawal authorizations.
class AccountProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient.instance;

  // Dashboard
  DashboardData? _dashboardData;
  bool _isDashboardLoading = false;
  String? _dashboardError;

  // Pending Withdrawal Authorization
  PendingWithdrawal? _pendingWithdrawal;
  bool _isAuthorizingWithdrawal = false;

  // Transaction history
  final List<Transaction> _transactions = [];
  int _currentPage = 1;
  int _lastPage = 1;
  bool _isHistoryLoading = false;
  String? _historyError;

  // Account info
  Map<String, dynamic>? _accountInfo;
  bool _isAccountLoading = false;

  // Getters
  DashboardData? get dashboardData => _dashboardData;
  bool get isDashboardLoading => _isDashboardLoading;
  String? get dashboardError => _dashboardError;

  PendingWithdrawal? get pendingWithdrawal =>
      _pendingWithdrawal ?? _dashboardData?.pendingWithdrawal;
  bool get isAuthorizingWithdrawal => _isAuthorizingWithdrawal;

  List<Transaction> get transactions => List.unmodifiable(_transactions);
  bool get isHistoryLoading => _isHistoryLoading;
  bool get hasMoreTransactions => _currentPage < _lastPage;
  String? get historyError => _historyError;

  Map<String, dynamic>? get accountInfo => _accountInfo;
  bool get isAccountLoading => _isAccountLoading;

  /// Fetch dashboard summary data.
  Future<void> fetchDashboard() async {
    _isDashboardLoading = true;
    _dashboardError = null;
    notifyListeners();

    try {
      final response = await _api.getDashboard();
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        _dashboardData =
            DashboardData.fromJson(data['data'] as Map<String, dynamic>);
        _pendingWithdrawal = _dashboardData?.pendingWithdrawal;
        // Keep transaction history in sync
        fetchTransactionHistory();
      } else {
        _dashboardError = data['message'] as String? ?? 'Gagal memuat data.';
      }
    } on DioException catch (e) {
      _dashboardError = _extractError(e);
    } catch (e) {
      _dashboardError = 'Terjadi kesalahan.';
    } finally {
      _isDashboardLoading = false;
      notifyListeners();
    }
  }

  /// Check active pending withdrawal request without full screen loading.
  Future<void> checkPendingWithdrawal() async {
    try {
      final response = await _api.getPendingWithdrawal();
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        if (data['data'] != null) {
          _pendingWithdrawal = PendingWithdrawal.fromJson(
            data['data'] as Map<String, dynamic>,
          );
        } else {
          _pendingWithdrawal = null;
        }
        notifyListeners();
      }
    } catch (e) {
      debugPrint('checkPendingWithdrawal error: $e');
    }
  }

  /// Authorize pending withdrawal request with 6-digit PIN.
  Future<({bool success, String message})> authorizeWithdrawal({
    required int requestId,
    required String pin,
  }) async {
    _isAuthorizingWithdrawal = true;
    notifyListeners();

    try {
      final response = await _api.authorizeWithdrawal(
        requestId: requestId,
        pin: pin,
      );
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        _pendingWithdrawal = null;
        await fetchDashboard();
        return (
          success: true,
          message: data['message'] as String? ?? 'Penarikan berhasil disetujui.',
        );
      } else {
        return (
          success: false,
          message: data['message'] as String? ?? 'Gagal memproses otorisasi.',
        );
      }
    } on DioException catch (e) {
      return (
        success: false,
        message: _extractError(e),
      );
    } catch (e) {
      return (
        success: false,
        message: 'Terjadi kesalahan sistem.',
      );
    } finally {
      _isAuthorizingWithdrawal = false;
      notifyListeners();
    }
  }

  /// Reject pending withdrawal request.
  Future<bool> rejectWithdrawal(int requestId) async {
    try {
      final response = await _api.rejectWithdrawal(requestId);
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        _pendingWithdrawal = null;
        notifyListeners();
        return true;
      }
      return false;
    } catch (_) {
      return false;
    }
  }

  /// Fetch transaction history (first page).
  Future<void> fetchTransactionHistory() async {
    _isHistoryLoading = true;
    _historyError = null;
    _transactions.clear();
    _currentPage = 1;
    notifyListeners();

    try {
      final response = await _api.getTransactionHistory(page: 1);
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        final history = TransactionHistory.fromJson(
          data['data'] as Map<String, dynamic>,
        );
        _transactions.addAll(history.transactions);
        _currentPage = history.currentPage;
        _lastPage = history.lastPage;
      } else {
        _historyError = data['message'] as String? ?? 'Gagal memuat riwayat.';
      }
    } on DioException catch (e) {
      _historyError = _extractError(e);
    } catch (e) {
      _historyError = 'Terjadi kesalahan.';
    } finally {
      _isHistoryLoading = false;
      notifyListeners();
    }
  }

  /// Load next page of transaction history.
  Future<void> loadMoreTransactions() async {
    if (_isHistoryLoading || !hasMoreTransactions) return;

    _isHistoryLoading = true;
    notifyListeners();

    try {
      final nextPage = _currentPage + 1;
      final response = await _api.getTransactionHistory(page: nextPage);
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        final history = TransactionHistory.fromJson(
          data['data'] as Map<String, dynamic>,
        );
        _transactions.addAll(history.transactions);
        _currentPage = history.currentPage;
        _lastPage = history.lastPage;
      }
    } catch (_) {
      // Silently fail on load more.
    } finally {
      _isHistoryLoading = false;
      notifyListeners();
    }
  }

  /// Fetch account/rekening information.
  Future<void> fetchAccountInfo() async {
    _isAccountLoading = true;
    notifyListeners();

    try {
      final response = await _api.getAccountInfo();
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        _accountInfo = data['data'] as Map<String, dynamic>;
      }
    } catch (_) {
      // Silently fail.
    } finally {
      _isAccountLoading = false;
      notifyListeners();
    }
  }

  /// Clear all data (on logout).
  void clear() {
    _dashboardData = null;
    _pendingWithdrawal = null;
    _transactions.clear();
    _accountInfo = null;
    _currentPage = 1;
    _lastPage = 1;
    notifyListeners();
  }

  String _extractError(DioException e) {
    if (e.response?.data is Map<String, dynamic>) {
      return (e.response!.data as Map<String, dynamic>)['message'] as String? ??
          'Terjadi kesalahan.';
    }
    return 'Tidak dapat terhubung ke server.';
  }
}
