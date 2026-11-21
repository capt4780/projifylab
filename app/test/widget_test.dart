import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:projify/models/mini_app.dart';
import 'package:projify/widgets/common.dart';

void main() {
  test('MiniApp đọc đúng dữ liệu từ API', () {
    final app = MiniApp.fromApi({
      'id': 7,
      'title': 'Thanh Trieu Health',
      'url': '',
      'status': 'dev',
      'glyph': '',
      'gradient': ['#2F5BFF', '#7C3AED'],
      'image': '',
    });
    expect(app.id, 7);
    expect(app.canOpen, isFalse);
    expect(app.displayGlyph, 'TT');
    expect(MiniApp.decode(app.encode())?.title, 'Thanh Trieu Health');
  });

  testWidgets('AppTile hiện chữ khi chưa có ảnh', (tester) async {
    const app = MiniApp(id: 1, title: 'Wiflow', url: 'https://example.com', active: true, glyph: 'W', color1: '#06B6D4', color2: '#2F5BFF', image: '');
    await tester.pumpWidget(const MaterialApp(home: Scaffold(body: AppTile(app: app, size: 60))));
    expect(find.text('W'), findsOneWidget);
  });
}
