import 'package:shared_preferences/shared_preferences.dart';

import '../models/mini_app.dart';

/// Lưu các lựa chọn của người dùng trên máy.
class AppPrefs {
  static const _kOnboarded = 'onboarded';
  static const _kDefaultApp = 'default_app';

  static Future<SharedPreferences> get _p => SharedPreferences.getInstance();

  static Future<bool> isOnboarded() async => (await _p).getBool(_kOnboarded) ?? false;

  static Future<void> setOnboarded() async => (await _p).setBool(_kOnboarded, true);

  static Future<MiniApp?> getDefaultApp() async => MiniApp.decode((await _p).getString(_kDefaultApp));

  static Future<void> setDefaultApp(MiniApp app) async => (await _p).setString(_kDefaultApp, app.encode());

  static Future<void> clearDefaultApp() async => (await _p).remove(_kDefaultApp);
}
