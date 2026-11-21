import 'dart:convert';

/// Một mini web app trên Projify Lab.
class MiniApp {
  final int id;
  final String title;
  final String url;
  final bool active;
  final String glyph;
  final String color1;
  final String color2;
  final String image;

  const MiniApp({
    required this.id,
    required this.title,
    required this.url,
    required this.active,
    required this.glyph,
    required this.color1,
    required this.color2,
    required this.image,
  });

  /// Mở được khi đang hoạt động và có link.
  bool get canOpen => active && url.isNotEmpty;

  /// Từ API /wp-json/projify/v1/apps.
  factory MiniApp.fromApi(Map<String, dynamic> j) {
    final gradient = (j['gradient'] is List) ? (j['gradient'] as List) : const [];
    return MiniApp(
      id: _toInt(j['id']),
      title: (j['title'] ?? '').toString(),
      url: (j['url'] ?? '').toString(),
      active: j['status'] == 'active',
      glyph: (j['glyph'] ?? '').toString(),
      color1: gradient.isNotEmpty ? gradient[0].toString() : '#2F5BFF',
      color2: gradient.length > 1 ? gradient[1].toString() : '#7C3AED',
      image: (j['image'] ?? '').toString(),
    );
  }

  /// Từ trang web khi người dùng bấm card (cầu nối JavaScript).
  factory MiniApp.fromBridge(Map<String, dynamic> j) {
    return MiniApp(
      id: _toInt(j['id']),
      title: (j['title'] ?? '').toString(),
      url: (j['url'] ?? '').toString(),
      active: true,
      glyph: (j['glyph'] ?? '').toString(),
      color1: (j['g1'] ?? '#2F5BFF').toString(),
      color2: (j['g2'] ?? '#7C3AED').toString(),
      image: (j['image'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'title': title,
        'url': url,
        'active': active,
        'glyph': glyph,
        'g1': color1,
        'g2': color2,
        'image': image,
      };

  factory MiniApp.fromJson(Map<String, dynamic> j) => MiniApp(
        id: _toInt(j['id']),
        title: (j['title'] ?? '').toString(),
        url: (j['url'] ?? '').toString(),
        active: j['active'] == true,
        glyph: (j['glyph'] ?? '').toString(),
        color1: (j['g1'] ?? '#2F5BFF').toString(),
        color2: (j['g2'] ?? '#7C3AED').toString(),
        image: (j['image'] ?? '').toString(),
      );

  String encode() => jsonEncode(toJson());

  static MiniApp? decode(String? s) {
    if (s == null || s.isEmpty) return null;
    try {
      return MiniApp.fromJson(jsonDecode(s) as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }

  /// Chữ hiển thị khi chưa có ảnh.
  String get displayGlyph {
    if (glyph.isNotEmpty) return glyph;
    final words = title.trim().split(RegExp(r'\s+')).where((w) => w.isNotEmpty).take(2);
    final g = words.map((w) => w.substring(0, 1).toUpperCase()).join();
    return g.isEmpty ? 'P' : g;
  }

  static int _toInt(dynamic v) => v is int ? v : int.tryParse('$v') ?? 0;
}
