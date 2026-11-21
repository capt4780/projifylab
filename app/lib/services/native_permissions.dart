import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';

/// Xin quyền hệ điều hành và cấp tự động cho mini app.
class NativePermissions {
  /// Danh sách quyền xin ở lần mở đầu tiên.
  static final List<Permission> onboarding = [
    Permission.camera,
    Permission.microphone,
    Permission.locationWhenInUse,
    Permission.notification,
    Permission.photos,
  ];

  /// Xin lần lượt tất cả quyền (hệ điều hành hiện hộp thoại cho từng quyền).
  static Future<void> requestAll() async {
    for (final p in onboarding) {
      try {
        final status = await p.status;
        if (!status.isGranted && !status.isPermanentlyDenied) {
          await p.request();
        }
      } catch (_) {
        // Một số máy không hỗ trợ quyền nào đó: bỏ qua.
      }
    }
  }

  /// Khi trang web xin camera/micro: xin quyền hệ điều hành nếu chưa có, rồi cấp cho trang.
  static Future<PermissionResponse> grantForWeb(PermissionRequest request) async {
    final needed = <Permission>{};
    for (final r in request.resources) {
      if (r == PermissionResourceType.CAMERA) needed.add(Permission.camera);
      if (r == PermissionResourceType.MICROPHONE) needed.add(Permission.microphone);
      if (r == PermissionResourceType.CAMERA_AND_MICROPHONE) {
        needed
          ..add(Permission.camera)
          ..add(Permission.microphone);
      }
    }
    for (final p in needed) {
      try {
        if (!(await p.isGranted)) await p.request();
      } catch (_) {}
    }
    return PermissionResponse(resources: request.resources, action: PermissionResponseAction.GRANT);
  }

  /// Lấy vị trí cho trang web (dùng thay navigator.geolocation để không hiện hộp thoại của WebView).
  static Future<Map<String, dynamic>> currentLocation(dynamic options) async {
    try {
      if (!await Geolocator.isLocationServiceEnabled()) {
        return {'ok': false, 'code': 2, 'message': 'Dịch vụ vị trí đang tắt'};
      }
      var perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied || perm == LocationPermission.deniedForever) {
        return {'ok': false, 'code': 1, 'message': 'Chưa cấp quyền vị trí'};
      }
      final high = options is Map && options['enableHighAccuracy'] == true;
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: LocationSettings(
          accuracy: high ? LocationAccuracy.best : LocationAccuracy.high,
          timeLimit: const Duration(seconds: 20),
        ),
      );
      return {
        'ok': true,
        'latitude': pos.latitude,
        'longitude': pos.longitude,
        'accuracy': pos.accuracy,
        'altitude': pos.altitude,
        'altitudeAccuracy': pos.altitudeAccuracy,
        'heading': pos.heading,
        'speed': pos.speed,
        'timestamp': pos.timestamp.millisecondsSinceEpoch,
      };
    } catch (e) {
      return {'ok': false, 'code': 3, 'message': 'Không lấy được vị trí'};
    }
  }

  /// Đoạn JavaScript chèn vào mini app: chuyển navigator.geolocation sang lấy vị trí qua app.
  static const String geolocationShim = r'''
(function () {
  if (window.__projifyGeo) return;
  window.__projifyGeo = true;
  function bridge() {
    return new Promise(function (resolve) {
      if (window.flutter_inappwebview && window.flutter_inappwebview.callHandler) { resolve(window.flutter_inappwebview); return; }
      window.addEventListener('flutterInAppWebViewPlatformReady', function () { resolve(window.flutter_inappwebview); }, { once: true });
    });
  }
  function toPosition(r) {
    return {
      coords: {
        latitude: r.latitude, longitude: r.longitude, accuracy: r.accuracy,
        altitude: r.altitude, altitudeAccuracy: r.altitudeAccuracy,
        heading: r.heading, speed: r.speed
      },
      timestamp: r.timestamp || Date.now()
    };
  }
  function getOnce(success, error, options) {
    bridge().then(function (b) { return b.callHandler('projifyGetLocation', options || {}); })
      .then(function (r) {
        if (r && r.ok) { if (success) success(toPosition(r)); }
        else if (error) error({ code: (r && r.code) || 2, message: (r && r.message) || 'Không lấy được vị trí', PERMISSION_DENIED: 1, POSITION_UNAVAILABLE: 2, TIMEOUT: 3 });
      })
      .catch(function () { if (error) error({ code: 2, message: 'Không lấy được vị trí', PERMISSION_DENIED: 1, POSITION_UNAVAILABLE: 2, TIMEOUT: 3 }); });
  }
  var watches = {}; var nextId = 1;
  try {
    var geo = navigator.geolocation;
    if (!geo) return;
    geo.getCurrentPosition = function (s, e, o) { getOnce(s, e, o); };
    geo.watchPosition = function (s, e, o) {
      var id = nextId++;
      getOnce(s, e, o);
      watches[id] = setInterval(function () { getOnce(s, e, o); }, 5000);
      return id;
    };
    geo.clearWatch = function (id) { clearInterval(watches[id]); delete watches[id]; };
  } catch (err) {}
})();
''';
}
