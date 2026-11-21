import 'package:flutter/material.dart';

import '../config.dart';
import '../models/mini_app.dart';

/// Nền sáng tím nhạt có các vầng màu mờ, giống trang chủ web.
class BrandBackground extends StatelessWidget {
  final Widget child;
  const BrandBackground({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(builder: (context, c) {
      final w = c.maxWidth, h = c.maxHeight;
      Widget blob(double left, double top, double bw, double bh, Color color) => Positioned(
            left: left,
            top: top,
            width: bw,
            height: bh,
            child: IgnorePointer(
              child: DecoratedBox(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(colors: [color, color.withValues(alpha: 0)]),
                ),
              ),
            ),
          );
      return ColoredBox(
        color: AppColors.base,
        child: Stack(children: [
          blob(-0.2 * w, -0.2 * h, 0.7 * w, 0.6 * h, const Color(0x7360A5FA)),
          blob(0.55 * w, 0.25 * h, 0.7 * w, 0.6 * h, const Color(0x73A78BFA)),
          blob(0.1 * w, 0.75 * h, 0.8 * w, 0.5 * h, const Color(0x5967E8F9)),
          Positioned.fill(child: child),
        ]),
      );
    });
  }
}

/// Chữ "Projify Lab".
class BrandWordmark extends StatelessWidget {
  final double size;
  const BrandWordmark({super.key, this.size = 19});

  @override
  Widget build(BuildContext context) {
    return Text.rich(
      TextSpan(children: [
        const TextSpan(text: 'Projify ', style: TextStyle(color: AppColors.ink)),
        const TextSpan(text: 'Lab', style: TextStyle(color: AppColors.blue)),
      ]),
      style: TextStyle(fontSize: size, fontWeight: FontWeight.w800, letterSpacing: -0.4),
    );
  }
}

/// Logo chữ P.
class BrandMark extends StatelessWidget {
  final double size;
  const BrandMark({super.key, this.size = 30});

  @override
  Widget build(BuildContext context) =>
      Image.asset('assets/brand/logo.png', width: size, height: size, fit: BoxFit.contain);
}

/// Icon vuông bo góc của một mini app (ảnh nếu có, ngược lại là gradient + chữ).
class AppTile extends StatelessWidget {
  final MiniApp app;
  final double size;
  final double? radius;
  final bool shadow;
  const AppTile({super.key, required this.app, this.size = 36, this.radius, this.shadow = false});

  @override
  Widget build(BuildContext context) {
    final r = radius ?? size * 0.32;
    final c1 = parseHexColor(app.color1, AppColors.blue);
    final c2 = parseHexColor(app.color2, AppColors.violet);
    final glyph = Center(
      child: Text(
        app.displayGlyph,
        maxLines: 1,
        style: TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w800,
          fontSize: size * (app.displayGlyph.length > 1 ? 0.36 : 0.44),
          letterSpacing: -0.5,
        ),
      ),
    );
    return Container(
      width: size,
      height: size,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(r),
        gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [c1, c2]),
        boxShadow: shadow
            ? [BoxShadow(color: c2.withValues(alpha: 0.45), blurRadius: size * 0.35, offset: Offset(0, size * 0.15))]
            : null,
      ),
      child: app.image.isEmpty
          ? glyph
          : Image.network(app.image, fit: BoxFit.cover, errorBuilder: (_, __, ___) => glyph),
    );
  }
}

/// Nút chính dạng viên thuốc có gradient.
class GradientButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  const GradientButton({super.key, required this.label, this.onPressed});

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        gradient: AppColors.brandGradient,
        borderRadius: BorderRadius.circular(999),
        boxShadow: const [BoxShadow(color: Color(0x664F46E5), blurRadius: 24, offset: Offset(0, 12))],
      ),
      child: Material(
        type: MaterialType.transparency,
        child: InkWell(
          borderRadius: BorderRadius.circular(999),
          onTap: onPressed,
          child: SizedBox(
            height: 54,
            width: double.infinity,
            child: Center(
              child: Text(label, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
            ),
          ),
        ),
      ),
    );
  }
}

/// Nút phụ nền tím nhạt.
class SoftButton extends StatelessWidget {
  final String label;
  final VoidCallback? onPressed;
  const SoftButton({super.key, required this.label, this.onPressed});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 50,
      width: double.infinity,
      child: TextButton(
        onPressed: onPressed,
        style: TextButton.styleFrom(
          backgroundColor: AppColors.base,
          foregroundColor: AppColors.blueDeep,
          shape: const StadiumBorder(),
          textStyle: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
        ),
        child: Text(label),
      ),
    );
  }
}

/// Màn "Không có kết nối mạng".
class OfflineView extends StatelessWidget {
  final VoidCallback onRetry;
  const OfflineView({super.key, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return BrandBackground(
      child: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              Container(
                width: 112,
                height: 112,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.7),
                  borderRadius: BorderRadius.circular(36),
                  border: Border.all(color: Colors.white),
                  boxShadow: const [BoxShadow(color: Color(0x592F5BFF), blurRadius: 40, offset: Offset(0, 20))],
                ),
                child: const Icon(Icons.wifi_off_rounded, size: 52, color: AppColors.blueDeep),
              ),
              const SizedBox(height: 22),
              const Text('Không có kết nối mạng',
                  style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: AppColors.ink)),
              const SizedBox(height: 10),
              const Text('Kiểm tra Wi‑Fi hoặc dữ liệu di động rồi thử lại nhé.',
                  textAlign: TextAlign.center, style: TextStyle(fontSize: 15, height: 1.5, color: AppColors.ink2)),
              const SizedBox(height: 26),
              SizedBox(width: 200, child: GradientButton(label: 'Thử lại', onPressed: onRetry)),
            ]),
          ),
        ),
      ),
    );
  }
}
