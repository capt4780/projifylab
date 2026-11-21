# Projify Lab – wp-content

Trang chủ: https://projifylab.com/apps/

Thư mục này gồm hai phần, dùng chung với nhau:

| Thư mục | Vai trò |
|---|---|
| `wp-content/plugins/projify-apps/` | Plugin **Projify Apps**: quản lý mini app trong wp-admin, cung cấp API cho app Android/iOS |
| `wp-content/themes/projifylab/` | Theme **Projifylab**: trang chủ dạng launcher theo mockup đã chốt |

## Cài đặt

1. Chép `plugins/projify-apps` vào `wp-content/plugins/` và `themes/projifylab` vào `wp-content/themes/` trên hosting.
   Cách khác là nén từng thư mục thành zip rồi tải lên ở **Plugin › Cài mới › Tải plugin lên** và **Giao diện › Giao diện › Thêm mới › Tải lên**.
2. Bật plugin **Projify Apps** trước. Lần bật đầu tiên, plugin tự tạo 8 mini app mẫu, tất cả ở trạng thái **Đang phát triển**:
   Thanh Trieu Health (nổi bật), Wiflow, Mini app 3 → 8.
3. Bật theme **Projifylab**. Trang chủ tự hiển thị launcher, không cần chỉnh gì thêm.

Yêu cầu: WordPress 6.0 trở lên, PHP 7.4 trở lên.

## Quản lý mini app (wp-admin › Mini app)

Mỗi mini app có:

- **Tên**: tiêu đề bài.
- **Trạng thái**: *Đang phát triển* (card mờ, có ổ khóa, không mở được) hoặc *Hoạt động* (mở được).
- **Link mini app**: trang sẽ mở khi bấm card. Chỉ dùng khi trạng thái là *Hoạt động*; khi đang phát triển, link không được đưa ra ngoài trang web hay API.
- **Nổi bật**: app hiện ở card siêu to trên cùng. Chỉ một app được nổi bật.
- **Chữ trên card**: tối đa 3 ký tự, hiện khi chưa có ảnh.
- **Màu card**: 8 bộ gradient lấy từ logo.
- **Ảnh vuông 1:1** (khung bên phải): có ảnh thì ảnh thay cho màu và chữ.
- **Mô tả ngắn**: 1 dòng, hiện dưới tên trên card nổi bật.
- **Thứ tự**: ô "Thứ tự" trong khung Thuộc tính, số nhỏ đứng trước.

## Banner quảng cáo (wp-admin › Banner)

Banner là slide ngang tỉ lệ 2:1, nằm cạnh app nổi bật. Mỗi banner có:

- **Tiêu đề** và **Mô tả** (khung "Tóm tắt").
- **Ảnh banner 2:1** (khung bên phải), nên dùng 1600 × 800 px. Chưa có ảnh thì banner dùng **Màu nền** đã chọn.
- **Link** và tuỳ chọn **Mở ở tab mới**. Để trống link thì banner chỉ để xem và không hiện nút.
- **Chữ trên nút** (mặc định "Xem ngay").
- **Hiện chữ trên banner**: bỏ chọn khi chữ đã nằm sẵn trong ảnh, banner sẽ chỉ hiện ảnh.
- **Bật/tắt** và **Thứ tự** (số nhỏ chạy trước).

Khi có từ 2 banner trở lên, slide tự chuyển sau mỗi 4,5 giây. Người xem chuyển được bằng cách vuốt ngang, bấm chấm tròn hoặc bấm mũi tên (mũi tên chỉ hiện khi banner đủ lớn). Slide tạm dừng khi rê chuột vào banner.
Plugin tạo sẵn 1 banner mẫu, sửa hoặc xoá tuỳ ý. Nếu tắt hết banner, trang chủ quay về bố cục cũ, khi đó card nổi bật nằm trong lưới.

## Bố cục trang chủ

Trang tự đổi bố cục theo màn hình và khi xoay máy:

| Màn hình | Hàng trên | Lưới app bên dưới |
|---|---|---|
| Điện thoại dọc | App nổi bật (vuông, rộng hết màn), banner 2:1 nằm ngay dưới | 2 cột |
| Điện thoại ngang | App nổi bật + banner rộng gấp đôi | 4 cột |
| Tablet / PC dọc | App nổi bật + banner rộng gấp đôi | 3 cột |
| Tablet / PC ngang | App nổi bật + banner rộng gấp đôi | 4 cột |

Ở màn ngang rất rộng, chiều rộng trang được giới hạn theo chiều cao màn hình, để hàng trên luôn nằm gọn trong một màn hình.

## API cho app Projify

`GET /wp-json/projify/v1/apps`

```json
{
  "version": "1.1.0",
  "apps": [
    {
      "id": 12,
      "title": "Thanh Trieu Health",
      "description": "",
      "url": "",
      "status": "dev",
      "featured": true,
      "glyph": "TT",
      "gradient": ["#2F5BFF", "#7C3AED"],
      "image": "",
      "order": 0
    }
  ]
}
```

Response có thêm khoá `banners` (danh sách banner đang bật). Muốn lấy riêng banner thì gọi `GET /wp-json/projify/v1/banners`.

- `status`: `dev` (đang phát triển) hoặc `active` (hoạt động).
- `url` để trống khi app đang phát triển.
- Trang web nhận biết đang mở trong app Projify khi User-Agent có chữ `ProjifyApp`, và thêm class `in-projify-app` vào `<body>`.

## Logo

Theme có sẵn logo chữ P. Muốn đổi, vào **Giao diện › Tùy biến › Nhận dạng trang** và chọn Logo.
