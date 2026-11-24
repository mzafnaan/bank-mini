import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../core/network/api_client.dart';
import '../models/dashboard_data.dart';
import '../models/pending_withdrawal.dart';
import '../models/stats_data.dart';
import '../models/transaction.dart';

/// Manages dashboard data, transaction history, account info, monthly stats, and deactivation approval.
class AccountProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient.instance;

  // Dashboard
  DashboardData? _dashboardData;
  bool _isDashboardLoading = false;
  String? _dashboardError;

  // Pending Withdrawal Authorization
  PendingWithdrawal? _pendingWithdrawal;
  bool _isAuthorizingWithdrawal = false;

  // Monthly Stats & Analytics
  MonthlyStatsData? _statsData;
  bool _isStatsLoading = false;
  String? _statsError;
  DateTime _selectedStatsMonth = DateTime.now();

  // Deactivation processing
  bool _isProcessingDeactivation = false;

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

  MonthlyStatsData? get statsData => _statsData;
  bool get isStatsLoading => _isStatsLoading;
  String? get statsError => _statsError;
  DateTime get selectedStatsMonth => _selectedStatsMonth;

  bool get isProcessingDeactivation => _isProcessingDeactivation;

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

  // ─── Monthly Stats & Analytics ───────────────────────────────

  /// Fetch monthly financial statistics for the donut / pie chart dashboard.
  Future<void> fetchStats([DateTime? month]) async {
    if (month != null) {
      _selectedStatsMonth = month;
    }
    final monthParam = DateFormat('yyyy-MM').format(_selectedStatsMonth);

    _isStatsLoading = true;
    _statsError = null;
    notifyListeners();

    try {
      final response = await _api.getStats(month: monthParam);
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        _statsData = MonthlyStatsData.fromJson(
          data['data'] as Map<String, dynamic>,
        );
      } else {
        _statsError = data['message'] as String? ?? 'Gagal memuat statistik.';
      }
    } on DioException catch (e) {
      _statsError = _extractError(e);
    } catch (e) {
      _statsError = 'Terjadi kesalahan sistem.';
    } finally {
      _isStatsLoading = false;
      notifyListeners();
    }
  }

  /// Change active month filter and re-fetch stats.
  void changeStatsMonth(DateTime newMonth) {
    _selectedStatsMonth = newMonth;
    fetchStats(newMonth);
  }

  // ─── Customer Deactivation Approval ──────────────────────────

  /// Customer approves account deactivation request.
  Future<({bool success, String message})> approveDeactivation() async {
    _isProcessingDeactivation = true;
    notifyListeners();

    try {
      final response = await _api.approveDeactivation();
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        clear();
        return (
          success: true,
          message: data['message'] as String? ??
              'Rekening Anda telah resmi dinonaktifkan.',
        );
      } else {
        return (
          success: false,
          message: data['message'] as String? ?? 'Gagal memproses permohonan.',
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
      _isProcessingDeactivation = false;
      notifyListeners();
    }
  }

  /// Customer rejects account deactivation request.
  Future<({bool success, String message})> rejectDeactivation() async {
    _isProcessingDeactivation = true;
    notifyListeners();

    try {
      final response = await _api.rejectDeactivation();
      final data = response.data as Map<String, dynamic>;

      if (data['success'] == true) {
        // Refresh dashboard so the pending card disappears
        await fetchDashboard();
        return (
          success: true,
          message: data['message'] as String? ??
              'Pengajuan penonaktifan rekening berhasil ditolak.',
        );
      } else {
        return (
          success: false,
          message: data['message'] as String? ?? 'Gagal menolak permohonan.',
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
      _isProcessingDeactivation = false;
      notifyListeners();
    }
  }

  /// Clear all data (on logout).
  void clear() {
    _dashboardData = null;
    _pendingWithdrawal = null;
    _transactions.clear();
    _accountInfo = null;
    _statsData = null;
    _currentPage = 1;
    _lastPage = 1;
    notifyListeners();
  }

  String _extractError(DioException e) {
    if (e.response?.statusCode == 429) {
      return 'Terlalu banyak permintaan. Harap tunggu beberapa detik sebelum mencoba lagi.';
    }
    if (e.response?.data is Map<String, dynamic>) {
      final msg =
          (e.response!.data as Map<String, dynamic>)['message'] as String? ??
              '';
      if (msg.toLowerCase().contains('too many attempts')) {
        return 'Terlalu banyak percobaan. Harap tunggu beberapa detik sebelum mencoba lagi.';
      }
      if (msg.isNotEmpty) return msg;
    }
    return 'Tidak dapat terhubung ke server.';
  }
}
