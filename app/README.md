# App Projify Lab (Android + iOS)

App Flutter, package `com.projifylab.app`, mở launcher `https://projifylab.com/apps/`.

## Luồng sử dụng

1. Lần đầu mở app: màn chào và xin quyền một lượt (camera, micro, vị trí, thông báo, ảnh).
2. Màn hình chính: launcher của web, hiển thị trong WebView.
3. Chọn một mini app: app hỏi "Đặt … làm ứng dụng mặc định?" rồi mở mini app toàn màn hình.
4. Mini app có topbar cố định: icon và tên app bên trái; nút tải lại và nút ✕ (về màn hình chính) bên phải.
   Quyền camera, micro và vị trí được cấp tự động. `navigator.geolocation` được chuyển sang lấy vị trí qua app.
5. Lần sau mở app:
   - Nếu có app mặc định: vào thẳng app đó (vẫn có nút "Chọn ứng dụng khác").
   - Nếu app mặc định đã bị xoá hoặc chuyển về "Đang phát triển": quay về màn hình chính.
6. Mất mạng: hiện màn "Không có kết nối" và nút Thử lại.

Link ngoài launcher (ví dụ banner quảng cáo) mở bằng trình duyệt của máy. Các link `tel:`, `mailto:`… mở bằng app tương ứng.

## Build

Mỗi lần đẩy code trong `app/` lên nhánh `main`, GitHub Actions (workflow **App Projify**) tự build:

- `projify-android-apk`: file APK để cài thử trên Android. Bản này ký bằng khoá debug.
- `projify-ios-unsigned`: bản iOS chưa ký. Muốn cài lên iPhone hoặc đưa lên App Store thì cần tài khoản Apple Developer.

Tải file ở tab **Actions** → chọn lần chạy → mục **Artifacts**.

Build trên máy (cần Flutter stable; build iOS cần macOS + Xcode):

```bash
cd app
bash scripts/bootstrap.sh     # tạo khung android/ios, icon, màn chờ
flutter build apk --release   # hoặc: flutter run
```

## Cấu hình

`lib/config.dart`: địa chỉ trang chủ, API và chuỗi User-Agent (`ProjifyApp/1.0`). Theme WordPress dựa vào chuỗi này để nhận biết đang chạy trong app.
