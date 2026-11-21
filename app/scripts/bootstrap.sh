#!/usr/bin/env bash
# Tạo phần khung Android/iOS còn thiếu rồi áp cấu hình riêng của Projify.
# Chạy một lần sau khi clone repo (GitHub Actions cũng chạy script này).
set -euo pipefail
cd "$(dirname "$0")/.."

echo "› Tạo khung dự án Flutter (không ghi đè file đã có)"
flutter create --org com.projifylab --project-name projify --platforms=android,ios --no-pub .

# flutter create sinh MainActivity ở gói com.projifylab.projify; app dùng com.projifylab.app.
rm -rf android/app/src/main/kotlin/com/projifylab/projify

echo "› Đặt applicationId Android = com.projifylab.app, minSdk 24"
G=android/app/build.gradle.kts
[ -f "$G" ] || G=android/app/build.gradle
sed -i.bak \
  -e 's/com\.projifylab\.projify/com.projifylab.app/g' \
  -e 's/minSdk = flutter\.minSdkVersion/minSdk = 24/' \
  -e 's/minSdkVersion flutter\.minSdkVersion/minSdkVersion 24/' \
  "$G"
rm -f "$G.bak"
grep -nE 'namespace|applicationId|minSdk' "$G" || true

echo "› Đặt bundle id iOS = com.projifylab.app"
if [ -f ios/Runner.xcodeproj/project.pbxproj ]; then
  sed -i.bak 's/com\.projifylab\.projify/com.projifylab.app/g' ios/Runner.xcodeproj/project.pbxproj
  rm -f ios/Runner.xcodeproj/project.pbxproj.bak
fi

if [ -f ios/Runner/Info.plist ] && command -v plutil >/dev/null 2>&1; then
  echo "› Ghi tên app và lời xin quyền vào Info.plist"
  P=ios/Runner/Info.plist
  plutil -replace CFBundleDisplayName -string "Projify Lab" "$P"
  plutil -replace NSCameraUsageDescription -string "Projify dùng camera để các mini app chụp ảnh và quét mã." "$P"
  plutil -replace NSMicrophoneUsageDescription -string "Projify dùng micro để các mini app ghi âm và gọi thoại, video." "$P"
  plutil -replace NSLocationWhenInUseUsageDescription -string "Projify dùng vị trí để các mini app gợi ý dịch vụ gần bạn." "$P"
  plutil -replace NSPhotoLibraryUsageDescription -string "Projify cần truy cập ảnh để bạn tải ảnh lên mini app." "$P"
  plutil -replace NSPhotoLibraryAddUsageDescription -string "Projify lưu ảnh từ mini app vào thư viện của bạn." "$P"
fi

echo "› Tải thư viện"
flutter pub get

echo "› Vá các plugin còn dùng proguard-android.txt (AGP 9 không còn hỗ trợ)"
python3 - <<'PY'
import json, pathlib, urllib.parse
cfg = json.loads(pathlib.Path('.dart_tool/package_config.json').read_text())
for pkg in cfg.get('packages', []):
    uri = pkg.get('rootUri', '')
    if not uri.startswith('file://'):
        continue
    root = pathlib.Path(urllib.parse.unquote(uri[len('file://'):]))
    for name in ('build.gradle', 'build.gradle.kts'):
        f = root / 'android' / name
        if f.is_file():
            txt = f.read_text()
            if "proguard-android.txt" in txt:
                f.write_text(txt.replace("proguard-android.txt", "proguard-android-optimize.txt"))
                print('  đã vá', pkg['name'])
PY

echo "› Tạo icon app và màn chờ"
dart run flutter_launcher_icons
dart run flutter_native_splash:create

echo "✓ Xong. Build Android: flutter build apk --release"
