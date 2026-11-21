import 'package:flutter/material.dart';

import '../models/mini_app.dart';
import '../services/app_prefs.dart';
import '../services/app_repository.dart';
import '../widgets/common.dart';
import 'default_launch_screen.dart';
import 'home_screen.dart';
import 'onboarding_screen.dart';

/// Màn chờ: quyết định vào màn xin quyền, màn hình chính hay app mặc định.
class StartupScreen extends StatefulWidget {
  const StartupScreen({super.key});

  @override
  State<StartupScreen> createState() => _StartupScreenState();
}

class _StartupScreenState extends State<StartupScreen> {
  @override
  void initState() {
    super.initState();
    _decide();
  }

  void _go(Widget screen) {
    if (!mounted) return;
    Navigator.of(context).pushReplacement(PageRouteBuilder(
      pageBuilder: (_, __, ___) => screen,
      transitionsBuilder: (_, a, __, child) => FadeTransition(opacity: a, child: child),
      transitionDuration: const Duration(milliseconds: 250),
    ));
  }

  Future<void> _decide() async {
    if (!await AppPrefs.isOnboarded()) {
      _go(const OnboardingScreen());
      return;
    }

    final saved = await AppPrefs.getDefaultApp();
    if (saved == null) {
      AppRepository.fetch().catchError((_) => <MiniApp>[]); // tải sẵn danh sách
      _go(const HomeScreen());
      return;
    }

    var target = saved;
    try {
      await AppRepository.fetch(timeout: const Duration(seconds: 4));
      final fresh = AppRepository.findById(saved.id);
      if (fresh == null || !fresh.canOpen) {
        // App mặc định đã bị xoá hoặc chuyển về "Đang phát triển".
        await AppPrefs.clearDefaultApp();
        _go(const HomeScreen());
        return;
      }
      target = fresh;
      await AppPrefs.setDefaultApp(fresh);
    } catch (_) {
      // Không có mạng: vẫn mở app mặc định đã lưu, màn mini app sẽ báo mất mạng nếu cần.
    }
    _go(DefaultLaunchScreen(app: target));
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: BrandBackground(
        child: Center(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            BrandMark(size: 84),
            SizedBox(height: 18),
            BrandWordmark(size: 30),
          ]),
        ),
      ),
    );
  }
}
