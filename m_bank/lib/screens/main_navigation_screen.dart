import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/account_provider.dart';
import '../widgets/withdrawal_pin_sheet.dart';
import 'account/account_screen.dart';
import 'history/history_screen.dart';
import 'home/home_screen.dart';
import 'security/security_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;

  const MainNavigationScreen({
    super.key,
    this.initialIndex = 0,
  });

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  late int _currentIndex;
  Timer? _pollingTimer;
  int? _promptedRequestId;
  bool _isPromptOpen = false;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _startGlobalPolling();
    });
  }

  void _startGlobalPolling() {
    // Check immediately
    _checkPendingAndPrompt();

    // Periodic check every 3 seconds
    _pollingTimer = Timer.periodic(const Duration(seconds: 3), (_) async {
      if (!mounted) return;
      await context.read<AccountProvider>().checkPendingWithdrawal();
      _checkPendingAndPrompt();
    });
  }

  void _checkPendingAndPrompt() {
    if (!mounted || _isPromptOpen) return;
    final pending = context.read<AccountProvider>().pendingWithdrawal;
    if (pending != null && pending.id != _promptedRequestId) {
      _promptedRequestId = pending.id;
      _isPromptOpen = true;
      WithdrawalPinSheet.show(context, pending).then((_) {
        _isPromptOpen = false;
      });
    }
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    super.dispose();
  }

  void _onTabSelected(int index) {
    setState(() {
      _currentIndex = index;
    });

    // Auto-refresh data on tab change so user always sees the latest state
    if (index == 0) {
      context.read<AccountProvider>().fetchDashboard();
    } else if (index == 1) {
      context.read<AccountProvider>().fetchTransactionHistory();
    } else if (index == 2) {
      context.read<AccountProvider>().fetchAccountInfo();
    }
  }

  @override
  Widget build(BuildContext context) {
    final List<Widget> screens = [
      HomeScreen(
        onViewAllTransactions: () => _onTabSelected(1),
      ),
      const HistoryScreen(),
      const AccountScreen(),
      const SecurityScreen(),
    ];

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: screens,
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          border: Border(
            top: BorderSide(color: Color(0xFFE5E7EB), width: 1),
          ),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: _onTabSelected,
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          elevation: 0,
          selectedItemColor: const Color(0xFF111827),
          unselectedItemColor: const Color(0xFF9CA3AF),
          selectedLabelStyle: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
          ),
          unselectedLabelStyle: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
          ),
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home_filled),
              label: 'Beranda',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.access_time_rounded),
              activeIcon: Icon(Icons.access_time_filled_rounded),
              label: 'Riwayat',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.grid_view_outlined),
              activeIcon: Icon(Icons.grid_view_rounded),
              label: 'Rekening',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.shield_outlined),
              activeIcon: Icon(Icons.shield_rounded),
              label: 'Keamanan',
            ),
          ],
        ),
      ),
    );
  }
}
