import 'package:flutter/material.dart';

/// Địa chỉ trang chủ Projify Lab (launcher các mini app).
const String kHomeUrl = 'https://projifylab.com/apps/';

/// API danh sách mini app do plugin Projify Apps cung cấp.
const String kAppsApi = '${kHomeUrl}wp-json/projify/v1/apps';

/// Gắn vào User-Agent để trang web biết đang chạy trong app Projify.
const String kUserAgentSuffix = 'ProjifyApp/1.0';

class AppColors {
  static const base = Color(0xFFEEF2FF);
  static const ink = Color(0xFF0B1636);
  static const ink2 = Color(0xFF334155);
  static const muted = Color(0xFF475569);
  static const blue = Color(0xFF2F5BFF);
  static const blueDeep = Color(0xFF1D3FD6);
  static const navy = Color(0xFF1E3A8A);
  static const violet = Color(0xFF7C3AED);

  static const brandGradient = LinearGradient(colors: [blue, violet]);
}

/// Chuyển "#RRGGBB" thành Color, sai định dạng thì dùng màu mặc định.
Color parseHexColor(String? hex, Color fallback) {
  if (hex == null) return fallback;
  var h = hex.trim().replaceFirst('#', '');
  if (h.length == 3) {
    h = h.split('').map((c) => '$c$c').join();
  }
  if (h.length != 6) return fallback;
  final v = int.tryParse(h, radix: 16);
  return v == null ? fallback : Color(0xFF000000 | v);
}
