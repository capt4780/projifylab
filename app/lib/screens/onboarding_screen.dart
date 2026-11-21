import 'package:flutter/material.dart';

import '../config.dart';
import '../services/app_prefs.dart';
import '../services/native_permissions.dart';
import '../widgets/common.dart';
import 'home_screen.dart';

/// Lần mở đầu tiên: giới thiệu và xin quyền một lượt.
class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  bool _busy = false;

  static const _items = <(IconData, List<Color>, String, String)>[
    (Icons.photo_camera_outlined, [Color(0xFF2F5BFF), Color(0xFF7C3AED)], 'Camera', 'Chụp ảnh, quét mã trong mini app'),
    (Icons.mic_none_rounded, [Color(0xFF6366F1), Color(0xFFA855F7)], 'Micro', 'Ghi âm, gọi thoại và video'),
    (Icons.location_on_outlined, [Color(0xFF06B6D4), Color(0xFF2F5BFF)], 'Vị trí', 'Gợi ý dịch vụ gần bạn'),
    (Icons.notifications_none_rounded, [Color(0xFF8B5CF6), Color(0xFFEC4899)], 'Thông báo', 'Nhận tin mới từ các mini app'),
    (Icons.photo_library_outlined, [Color(0xFF3B82F6), Color(0xFF22D3EE)], 'Ảnh và tệp', 'Tải ảnh, tài liệu lên mini app'),
  ];

  Future<void> _finish({required bool request}) async {
    if (_busy) return;
    setState(() => _busy = true);
    if (request) await NativePermissions.requestAll();
    await AppPrefs.setOnboarded();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const HomeScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: BrandBackground(
        child: SafeArea(
          child: LayoutBuilder(builder: (context, c) {
            return SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(24, 28, 24, 24),
              child: ConstrainedBox(
                constraints: BoxConstraints(minHeight: c.maxHeight - 52),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 480),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      const BrandMark(size: 48),
                      const SizedBox(height: 18),
                      const Text.rich(
                        TextSpan(children: [
                          TextSpan(text: 'Chào mừng đến\n'),
                          TextSpan(text: 'Projify Lab', style: TextStyle(color: AppColors.blue)),
                        ]),
                        style: TextStyle(fontSize: 28, height: 1.2, fontWeight: FontWeight.w800, color: AppColors.ink, letterSpacing: -0.8),
                      ),
                      const SizedBox(height: 8),
                      const Text(
                        'Để các mini app chạy mượt, Projify cần vài quyền dưới đây. Bạn chỉ cần cho phép một lần.',
                        style: TextStyle(fontSize: 15, height: 1.55, color: AppColors.ink2),
                      ),
                      const SizedBox(height: 20),
                      for (final it in _items) _PermissionRow(icon: it.$1, colors: it.$2, title: it.$3, subtitle: it.$4),
                      const SizedBox(height: 24),
                      GradientButton(label: _busy ? 'Đang xin quyền…' : 'Cho phép tất cả', onPressed: _busy ? null : () => _finish(request: true)),
                      const SizedBox(height: 6),
                      SizedBox(
                        width: double.infinity,
                        height: 48,
                        child: TextButton(
                          onPressed: _busy ? null : () => _finish(request: false),
                          style: TextButton.styleFrom(foregroundColor: AppColors.blueDeep, textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                          child: const Text('Để sau'),
                        ),
                      ),
                      const Center(
                        child: Text('Bạn có thể đổi lại bất cứ lúc nào trong Cài đặt của máy.',
                            textAlign: TextAlign.center, style: TextStyle(fontSize: 12, color: AppColors.muted)),
                      ),
                    ]),
                  ),
                ),
              ),
            );
          }),
        ),
      ),
    );
  }
}

class _PermissionRow extends StatelessWidget {
  final IconData icon;
  final List<Color> colors;
  final String title;
  final String subtitle;
  const _PermissionRow({required this.icon, required this.colors, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.62),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withValues(alpha: 0.9)),
      ),
      child: Row(children: [
        Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(15), gradient: LinearGradient(colors: colors)),
          child: Icon(icon, color: Colors.white, size: 22),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700, color: AppColors.ink)),
            const SizedBox(height: 2),
            Text(subtitle, style: const TextStyle(fontSize: 13, color: AppColors.muted)),
          ]),
        ),
      ]),
    );
  }
}
