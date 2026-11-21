import 'dart:async';

import 'package:flutter/material.dart';

import '../config.dart';
import '../models/mini_app.dart';
import '../widgets/common.dart';
import 'home_screen.dart';
import 'mini_app_screen.dart';

/// Lần sau mở app khi đã có app mặc định: hiện icon app rồi vào thẳng.
class DefaultLaunchScreen extends StatefulWidget {
  final MiniApp app;
  const DefaultLaunchScreen({super.key, required this.app});

  @override
  State<DefaultLaunchScreen> createState() => _DefaultLaunchScreenState();
}

class _DefaultLaunchScreenState extends State<DefaultLaunchScreen> {
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer(const Duration(milliseconds: 900), _openApp);
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _openApp() {
    if (!mounted) return;
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => MiniAppScreen(app: widget.app)));
  }

  void _chooseOther() {
    _timer?.cancel();
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const HomeScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: BrandBackground(
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24, 28, 24, 32),
            child: Column(children: [
              const Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                BrandMark(size: 28),
                SizedBox(width: 8),
                BrandWordmark(size: 18),
              ]),
              const Spacer(),
              AppTile(app: widget.app, size: 132, radius: 40, shadow: true),
              const SizedBox(height: 18),
              Text(widget.app.title,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.ink, letterSpacing: -0.5)),
              const SizedBox(height: 16),
              const Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2.5, color: AppColors.blue)),
                SizedBox(width: 10),
                Text('Đang mở ứng dụng mặc định…', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.ink2)),
              ]),
              const Spacer(),
              TextButton(
                onPressed: _chooseOther,
                style: TextButton.styleFrom(
                  foregroundColor: AppColors.blueDeep,
                  backgroundColor: Colors.white.withValues(alpha: 0.7),
                  shape: const StadiumBorder(),
                  minimumSize: const Size(0, 44),
                  padding: const EdgeInsets.symmetric(horizontal: 18),
                  textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
                ),
                child: const Text('Chọn ứng dụng khác'),
              ),
            ]),
          ),
        ),
      ),
    );
  }
}
