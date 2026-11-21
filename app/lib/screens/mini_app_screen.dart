import 'dart:collection';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:url_launcher/url_launcher.dart';

import '../config.dart';
import '../models/mini_app.dart';
import '../services/native_permissions.dart';
import '../widgets/common.dart';
import 'home_screen.dart';

/// Mini app chạy toàn màn hình, có topbar cố định:
/// trái là icon + tên app, phải là nút tải lại và nút thoát về màn hình chính.
class MiniAppScreen extends StatefulWidget {
  final MiniApp app;
  const MiniAppScreen({super.key, required this.app});

  @override
  State<MiniAppScreen> createState() => _MiniAppScreenState();
}

class _MiniAppScreenState extends State<MiniAppScreen> {
  InAppWebViewController? _web;
  double _progress = 0;
  bool _offline = false;

  /// Thoát về màn hình chính của Projify Lab.
  void _exit() {
    final nav = Navigator.of(context);
    if (nav.canPop()) {
      nav.pop();
    } else {
      // Mở thẳng từ app mặc định: chưa có màn hình chính phía sau.
      nav.pushReplacement(MaterialPageRoute(builder: (_) => const HomeScreen()));
    }
  }

  Future<void> _reload() async {
    if (_offline) {
      setState(() => _offline = false);
      final current = await _web?.getUrl();
      await _web?.loadUrl(urlRequest: URLRequest(url: current ?? WebUri(widget.app.url)));
    } else {
      await _web?.reload();
    }
  }

  Future<NavigationActionPolicy> _onNavigate(InAppWebViewController c, NavigationAction action) async {
    final uri = action.request.url;
    if (uri == null) return NavigationActionPolicy.ALLOW;
    final scheme = uri.scheme.toLowerCase();
    if (scheme == 'http' || scheme == 'https' || scheme == 'about' || scheme == 'data' || scheme == 'blob') {
      return NavigationActionPolicy.ALLOW;
    }
    // tel:, mailto:, intent:, zalo:… → mở bằng app tương ứng.
    await launchUrl(uri, mode: LaunchMode.externalApplication).catchError((_) => false);
    return NavigationActionPolicy.CANCEL;
  }

  @override
  Widget build(BuildContext context) {
    return AnnotatedRegion<SystemUiOverlayStyle>(
      value: SystemUiOverlayStyle.dark.copyWith(statusBarColor: Colors.transparent),
      child: PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, _) async {
          if (didPop) return;
          if (_web != null && await _web!.canGoBack()) {
            await _web!.goBack();
          } else {
            _exit();
          }
        },
        child: Scaffold(
          backgroundColor: Colors.white,
          body: Column(children: [
            _TopBar(app: widget.app, progress: _progress, onReload: _reload, onExit: _exit),
            Expanded(
              child: Stack(children: [
                InAppWebView(
                  initialUrlRequest: URLRequest(url: WebUri(widget.app.url)),
                  initialUserScripts: UnmodifiableListView<UserScript>([
                    UserScript(
                      source: NativePermissions.geolocationShim,
                      injectionTime: UserScriptInjectionTime.AT_DOCUMENT_START,
                    ),
                  ]),
                  initialSettings: InAppWebViewSettings(
                    applicationNameForUserAgent: kUserAgentSuffix,
                    useShouldOverrideUrlLoading: true,
                    mediaPlaybackRequiresUserGesture: false,
                    allowsInlineMediaPlayback: true,
                    geolocationEnabled: true,
                    javaScriptCanOpenWindowsAutomatically: true,
                    supportMultipleWindows: false,
                    allowsBackForwardNavigationGestures: true,
                    isInspectable: kDebugMode,
                  ),
                  onWebViewCreated: (c) {
                    _web = c;
                    c.addJavaScriptHandler(
                      handlerName: 'projifyGetLocation',
                      callback: (args) => NativePermissions.currentLocation(args.isNotEmpty ? args.first : null),
                    );
                  },
                  shouldOverrideUrlLoading: _onNavigate,
                  // Tự cấp quyền camera/micro cho mini app, không hiện hộp thoại.
                  onPermissionRequest: (c, request) => NativePermissions.grantForWeb(request),
                  // Android: tự cấp quyền vị trí của WebView.
                  onGeolocationPermissionsShowPrompt: (c, origin) async =>
                      GeolocationPermissionShowPromptResponse(origin: origin, allow: true, retain: true),
                  onProgressChanged: (c, p) {
                    if (mounted) setState(() => _progress = p / 100);
                  },
                  onReceivedError: (c, request, error) {
                    if (request.isForMainFrame != true) return;
                    if (error.type == WebResourceErrorType.CANCELLED) return;
                    if (mounted) setState(() => _offline = true);
                  },
                ),
                if (_offline) Positioned.fill(child: OfflineView(onRetry: _reload)),
              ]),
            ),
          ]),
        ),
      ),
    );
  }
}

class _TopBar extends StatelessWidget {
  final MiniApp app;
  final double progress;
  final VoidCallback onReload;
  final VoidCallback onExit;
  const _TopBar({required this.app, required this.progress, required this.onReload, required this.onExit});

  @override
  Widget build(BuildContext context) {
    final loading = progress > 0 && progress < 1;
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0x1A1D3FD6))),
        boxShadow: [BoxShadow(color: Color(0x261D3FD6), blurRadius: 18, offset: Offset(0, 6), spreadRadius: -12)],
      ),
      child: SafeArea(
        bottom: false,
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          SizedBox(
            height: 60,
            child: Padding(
              padding: const EdgeInsets.only(left: 14, right: 10),
              child: Row(children: [
                AppTile(app: app, size: 36, radius: 12),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    app.title,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: AppColors.ink, letterSpacing: -0.2),
                  ),
                ),
                _BarButton(icon: Icons.refresh_rounded, tooltip: 'Tải lại', onTap: onReload),
                const SizedBox(width: 10),
                _BarButton(icon: Icons.close_rounded, tooltip: 'Thoát ứng dụng', onTap: onExit),
              ]),
            ),
          ),
          SizedBox(
            height: 3,
            child: loading
                ? LinearProgressIndicator(
                    value: progress,
                    backgroundColor: Colors.transparent,
                    valueColor: const AlwaysStoppedAnimation(AppColors.blue),
                  )
                : null,
          ),
        ]),
      ),
    );
  }
}

class _BarButton extends StatelessWidget {
  final IconData icon;
  final String tooltip;
  final VoidCallback onTap;
  const _BarButton({required this.icon, required this.tooltip, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Tooltip(
      message: tooltip,
      child: Material(
        color: AppColors.base,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: SizedBox(width: 44, height: 44, child: Icon(icon, color: AppColors.blueDeep, size: 22)),
        ),
      ),
    );
  }
}
