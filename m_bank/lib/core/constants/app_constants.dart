/// API and app-wide constants for M-Bank.
class AppConstants {
  AppConstants._();

  /// App display name.
  static const String appName = 'Bank Mini';

  /// Base URL for the Laravel API.
  /// - Web (Chrome / Edge): http://127.0.0.1:8000
  /// - Physical Android phone (via USB with adb reverse): http://127.0.0.1:8000
  /// - Android Emulator: http://10.0.2.2:8000
  static String get baseUrl {
    return 'http://127.0.0.1:8000';
  }

  static const String apiPrefix = '/api/v1';

  /// Secure storage keys.
  static const String tokenKey = 'access_token';
  static const String rememberMeKey = 'remember_me';
  static const String savedUsernameKey = 'saved_username';
}
