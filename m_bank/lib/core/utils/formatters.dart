/// Utility class for formatting values in Indonesian locale.
class Formatters {
  Formatters._();

  static const List<String> _monthsShort = [
    'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
    'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
  ];

  static const List<String> _monthsFull = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];

  /// Format a number as Indonesian Rupiah currency.
  /// Example: 30000 -> "Rp 30.000"
  static String currency(num amount) {
    final integerPart = amount.toInt();
    final formatted = integerPart.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    );
    return 'Rp $formatted';
  }

  /// Format a number as signed currency for transaction display.
  /// Example: 30000, isCredit: true -> "+Rp 30.000"
  static String signedCurrency(num amount, {required bool isCredit}) {
    final prefix = isCredit ? '+' : '-';
    return '$prefix${currency(amount)}';
  }

  /// Format a DateTime to Indonesian locale date string.
  /// Example: "31 Okt 2026, 05:13"
  static String dateTime(DateTime dt) {
    final day = dt.day.toString().padLeft(2, '0');
    final month = _monthsShort[dt.month - 1];
    final year = dt.year;
    final hour = dt.hour.toString().padLeft(2, '0');
    final minute = dt.minute.toString().padLeft(2, '0');
    return '$day $month $year, $hour:$minute';
  }

  /// Format a DateTime to full date string.
  /// Example: "31 Oktober 2026, 05:13"
  static String dateTimeFull(DateTime dt) {
    final day = dt.day;
    final month = _monthsFull[dt.month - 1];
    final year = dt.year;
    final hour = dt.hour.toString().padLeft(2, '0');
    final minute = dt.minute.toString().padLeft(2, '0');
    return '$day $month $year, $hour:$minute';
  }

  /// Format a DateTime to date only.
  /// Example: "31 Oktober 2026"
  static String dateOnly(DateTime dt) {
    final day = dt.day;
    final month = _monthsFull[dt.month - 1];
    final year = dt.year;
    return '$day $month $year';
  }

  /// Format a DateTime to Month and Year.
  /// Example: "Oktober 2026"
  static String monthYear(DateTime dt) {
    final month = _monthsFull[dt.month - 1];
    final year = dt.year;
    return '$month $year';
  }
}
