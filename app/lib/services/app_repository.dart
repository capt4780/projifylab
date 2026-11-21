import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config.dart';
import '../models/mini_app.dart';

/// Lấy danh sách mini app từ WordPress.
class AppRepository {
  static List<MiniApp> _cache = const [];

  /// Danh sách lấy được lần gần nhất (có thể rỗng).
  static List<MiniApp> get cached => _cache;

  static Future<List<MiniApp>> fetch({Duration timeout = const Duration(seconds: 8)}) async {
    final res = await http
        .get(Uri.parse(kAppsApi), headers: {'Accept': 'application/json', 'User-Agent': kUserAgentSuffix})
        .timeout(timeout);
    if (res.statusCode != 200) {
      throw Exception('HTTP ${res.statusCode}');
    }
    final body = jsonDecode(utf8.decode(res.bodyBytes));
    final list = (body is Map && body['apps'] is List) ? body['apps'] as List : const [];
    _cache = list.whereType<Map>().map((m) => MiniApp.fromApi(Map<String, dynamic>.from(m))).toList();
    return _cache;
  }

  static MiniApp? findById(int id) {
    for (final a in _cache) {
      if (a.id == id) return a;
    }
    return null;
  }

  /// Tìm mini app theo link (bỏ qua dấu "/" cuối và phần #...).
  static MiniApp? findByUrl(String url) {
    String norm(String u) {
      var s = u.split('#').first;
      while (s.endsWith('/')) {
        s = s.substring(0, s.length - 1);
      }
      return s.toLowerCase();
    }

    final target = norm(url);
    for (final a in _cache) {
      if (a.canOpen && norm(a.url) == target) return a;
    }
    return null;
  }
}
