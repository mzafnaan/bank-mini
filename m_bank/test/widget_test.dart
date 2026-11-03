import 'package:flutter_test/flutter_test.dart';
import 'package:m_bank/main.dart';

void main() {
  testWidgets('App smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const MBankApp());
    expect(find.byType(MBankApp), findsOneWidget);
  });
}
