import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_inappwebview/flutter_inappwebview.dart';
import 'package:url_launcher/url_launcher.dart';

import '../config.dart';
import '../models/mini_app.dart';
import '../services/app_prefs.dart';
import '../services/app_repository.dart';
import '../widgets/common.dart';
import 'mini_app_screen.dart';

/// Màn hình chính: hiện launcher của projifylab.com/apps trong WebView.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> with WidgetsBindingObserver {
  InAppWebViewController? _web;
  bool _offline = false;
  bool _loading = true;
  bool _opening = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _refreshApps();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) _refreshApps();
  }

  Future<void> _refreshApps() async {
    try {
      await AppRepository.fetch();
    } catch (_) {}
  }

  bool _isLauncherUrl(Uri? uri) {
    if (uri == null) return false;
    final home = Uri.parse(kHomeUrl);
    return uri.host == home.host && uri.path.startsWith(home.path);
  }

  /// Người dùng chọn một mini app: hỏi đặt mặc định rồi mở toàn màn hình.
  Future<void> _openApp(MiniApp app) async {
    if (_opening || app.url.isEmpty) return;
    _opening = true;
    try {
      final makeDefault = await showModalBottomSheet<bool>(
        context: context,
        backgroundColor: Colors.white,
        showDragHandle: false,
        isScrollControlled: true,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
        builder: (_) => _AskDefaultSheet(app: app),
      );
      if (makeDefault == null || !mounted) return; // vuốt đóng: không mở
      if (makeDefault) {
        await AppPrefs.setDefaultApp(app);
      } else {
        await AppPrefs.clearDefaultApp();
      }
      if (!mounted) return;
      await Navigator.of(context).push(MaterialPageRoute(builder: (_) => MiniAppScreen(app: app)));
    } finally {
      _opening = false;
    }
  }

  Future<NavigationActionPolicy> _onNavigate(InAppWebViewController c, NavigationAction action) async {
    final uri = action.request.url;
    if (uri == null || !action.isForMainFrame) return NavigationActionPolicy.ALLOW;

    final scheme = uri.scheme.toLowerCase();
    if (scheme != 'http' && scheme != 'https') {
      // tel:, mailto:, zalo:… → mở bằng app tương ứng trên máy.
      await launchUrl(uri, mode: LaunchMode.externalApplication).catchError((_) => false);
      return NavigationActionPolicy.CANCEL;
    }

    // Dự phòng khi trang chưa gọi được cầu nối: nhận ra link của mini app.
    final app = AppRepository.findByUrl(uri.toString());
    if (app != null) {
      _openApp(app);
      return NavigationActionPolicy.CANCEL;
    }

    if (_isLauncherUrl(uri)) return NavigationActionPolicy.ALLOW;

    // Link ngoài (ví dụ banner quảng cáo) → mở bằng trình duyệt của máy.
    await launchUrl(uri, mode: LaunchMode.externalApplication).catchError((_) => false);
    return NavigationActionPolicy.CANCEL;
  }

  Future<void> _retry() async {
    setState(() {
      _offline = false;
      _loading = true;
    });
    await _web?.loadUrl(urlRequest: URLRequest(url: WebUri(kHomeUrl)));
    _refreshApps();
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
            await SystemNavigator.pop();
          }
        },
        child: Scaffold(
          backgroundColor: AppColors.base,
          body: Stack(children: [
            SafeArea(
              bottom: false,
              child: InAppWebView(
                initialUrlRequest: URLRequest(url: WebUri(kHomeUrl)),
                initialSettings: InAppWebViewSettings(
                  applicationNameForUserAgent: kUserAgentSuffix,
                  useShouldOverrideUrlLoading: true,
                  transparentBackground: true,
                  supportZoom: false,
                  isInspectable: kDebugMode,
                  mediaPlaybackRequiresUserGesture: false,
                  allowsInlineMediaPlayback: true,
                ),
                onWebViewCreated: (c) {
                  _web = c;
                  c.addJavaScriptHandler(
                    handlerName: 'projifyOpenApp',
                    callback: (args) {
                      if (args.isNotEmpty && args.first is Map) {
                        final app = MiniApp.fromBridge(Map<String, dynamic>.from(args.first as Map));
                        _openApp(app);
                      }
                      return true;
                    },
                  );
                },
                shouldOverrideUrlLoading: _onNavigate,
                onLoadStop: (c, url) {
                  if (mounted) setState(() => _loading = false);
                },
                onReceivedError: (c, request, error) {
                  if (request.isForMainFrame != true) return;
                  if (error.type == WebResourceErrorType.CANCELLED) return;
                  if (mounted) setState(() => _offline = true);
                },
              ),
            ),
            if (_loading && !_offline)
              const Positioned.fill(
                child: IgnorePointer(
                  child: BrandBackground(
                    child: Center(child: BrandMark(size: 72)),
                  ),
                ),
              ),
            if (_offline) Positioned.fill(child: OfflineView(onRetry: _retry)),
          ]),
        ),
      ),
    );
  }
}

/// Bảng hỏi "Đặt … làm ứng dụng mặc định?".
class _AskDefaultSheet extends StatelessWidget {
  final MiniApp app;
  const _AskDefaultSheet({required this.app});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(22, 26, 22, 22),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          AppTile(app: app, size: 84, radius: 26, shadow: true),
          const SizedBox(height: 16),
          Text(
            'Đặt ${app.title} làm ứng dụng mặc định?',
            textAlign: TextAlign.center,
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.ink, letterSpacing: -0.4),
          ),
          const SizedBox(height: 20),
          GradientButton(label: 'Đặt làm mặc định', onPressed: () => Navigator.of(context).pop(true)),
          const SizedBox(height: 10),
          SoftButton(label: 'Không, chỉ mở lần này', onPressed: () => Navigator.of(context).pop(false)),
        ]),
      ),
    );
  }
}
