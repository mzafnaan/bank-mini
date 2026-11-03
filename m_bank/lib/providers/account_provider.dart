import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../core/network/api_client.dart';
import '../models/dashboard_data.dart';
import '../models/transaction.dart';

/// Manages dashboard data, transaction history, and account info.
class AccountProvider extends ChangeNotifier {
  final ApiClient _api = ApiClient.instance;

  // Dashboard
  DashboardData? _dashboardData;
  bool _isDashboardLoading = false;
  String? _dashboardError;

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

