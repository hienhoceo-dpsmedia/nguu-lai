# Nhật ký Thay đổi (CHANGELOG.md)

Tất cả các thay đổi đáng chú ý của dự án **Ngưu Lai** sẽ được ghi lại trong tài liệu này.

Định dạng tài liệu tuân theo chuẩn [Keep a Changelog](https://keepachangelog.com/vi/1.0.0/), và dự án áp dụng quy tắc [Semantic Versioning (SemVer)](https://semver.org/spec/v2.0.0.html).

---

## [1.0.1] - 2026-08-18

### Đã thêm (Added)
- **Custom CSS Tùy chỉnh (Giao diện Shortcode):**
  - Tích hợp khung soạn thảo Custom CSS trong Admin Settings (*Mục 4: Custom CSS Tùy Chỉnh*).
  - Tự động nạp CSS vào frontend theo 2 tầng: `wp_add_inline_style()` vào `<head>` và `<style id="nguu-lai-custom-css">` trực tiếp trong output shortcode (tương thích 100% với các plugin caching/minification).
  - Tự động dọn dẹp tùy chọn `nguu_lai_custom_css` khi gỡ cài đặt (uninstall).

### Đã sửa (Fixed)
- **Sửa lỗi Google Login modal bị xung đột CSS class:**
  - Đổi tên các CSS class generic `modal-backdrop`, `modal-dialog` sang `nguu-lai-backdrop`, `nguu-lai-dialog` để tránh bị theme hoặc plugin khác (Bootstrap, WooCommerce, Elementor...) ghi đè hoặc xung đột z-index.
- **Sửa lỗi Google GSI script bị defer/optimize bởi cache plugin:**
  - Thêm thuộc tính `data-no-optimize` và `data-cfasync="false"` vào thẻ `<script>` Google Identity Services để các plugin cache/minify (WP Rocket, LiteSpeed Cache, Cloudflare Zaraz, Autoptimize...) không defer/async/minify script GSI, tránh lỗi `google.accounts.id is not defined`.

---

## [1.0.0] - 2026-08-18

### Đã thêm (Added)
- **Giao diện Meme Workbench 100% Thuần Việt:**
  - Khung Canvas 2D độ phân giải cao 900 × 900 xuất ảnh PNG sắc nét.
  - Tự động ngắt dòng thông minh (Smart text wrap) cho tiếng Việt có dấu.
  - Thanh trượt kích thước chữ (28px - 120px), vị trí ngang X (8% - 92%), vị trí dọc Y (8% - 92%).
  - 16 phôi ảnh WebP Ngưu Lai nguyên bản đóng gói sẵn trong `assets/memes/`.
  - Hỗ trợ tải ảnh cá nhân từ máy tính / điện thoại (`URL.createObjectURL`).
  - **Tích hợp 10 Google Fonts chuẩn 100% Tiếng Việt:** Montserrat, Be Vietnam Pro, Anton, Oswald, Bungee, Comfortaa, Caveat, Patrick Hand, Playfair Display, Roboto (hỗ trợ hiển thị trực quan ngay trên ô gõ chữ).
  - **Thư viện Câu thoại Viral cực hot:** Tích hợp sẵn các câu nói trend đình đám (*'Tau mà tổn thương thì tbây phải tổn thất', 'Cái đầu của tui tỉnh như tỉnh uỷ', 'Cái đồ không có nổi 1000 tỷ', 'Trùm mền tbây hết'...*) kèm nút Đổi câu ngẫu nhiên 🎲.
  - Bật/tắt watermark bản quyền động từ Admin.
  - Toàn bộ quá trình tạo ảnh được xử lý 100% Client-side trong trình duyệt (không tải ảnh người dùng lên máy chủ).
- **Tối ưu hóa Giao diện Di động (Mobile-First UX):**
  - Modal Đăng nhập Google thiết kế theo chuẩn **Native Bottom Sheet** trượt từ cạnh dưới lên với thanh kéo Drag Handle và góc bo 24px.
  - Avatar chú bò Ngưu Lai tròn viền vàng trung tâm và thẻ quyền lợi VIP.
  - Toàn bộ biểu tượng chuyển sang **100% Local & Inline SVG** (không gọi CDN bên ngoài).
- **Tích hợp Đăng nhập Google (Google One-Tap & OAuth Button):**
  - Xác thực Google ID Token phía Backend thông qua endpoint `/wp-json/nguu-lai/v1/google-login`.
  - Tự động tạo tài khoản WordPress và duy trì phiên đăng nhập an toàn (`wp_set_auth_cookie`).
  - Hệ thống Quota: Khách vãng lai bị giới hạn lượt tải mỗi ngày; Đăng nhập Google để mở khóa tải không giới hạn.
  - Modal Google Sign-In thông minh khi hết lượt tải miễn phí hoặc khi bật chế độ *Bắt buộc đăng nhập*.
- **Trang Quản trị Admin Dashboard 100% Thuần Việt:**
  - 5 Tabs điều hướng: *Tổng quan & Shortcode*, *Cài đặt Google & Hạn mức*, *Quản lý Câu thoại & Mẫu*, *Nhật ký & Thống kê*, *Bảo mật & Chặn IP*.
  - Soạn thảo danh sách câu thoại dạng Textarea đa dòng và nút **Khôi phục câu thoại viral mặc định 🔄**.
  - Thẻ KPI thống kê: Tổng số meme đã tạo, Lượng IP truy cập, Người dùng Google.
  - Bảng xếp hạng Top mẫu phôi được dùng nhiều nhất.
  - Bộ lọc nhật ký theo mốc thời gian (*Hôm nay, Hôm qua, 7 ngày, 30 ngày, Tùy chỉnh*) kèm phân trang.
  - Quản lý danh sách IP bị chặn và tin cậy Proxy/Cloudflare.
- **Cấu trúc & Tài liệu:**
  - Chuẩn OOP PSR-4 autoloading với Composer.
  - Toàn bộ tài liệu kỹ thuật: `README.md`, `ARCHITECTURE.md`, `SECURITY.md`, `CONTRIBUTING.md`, `HOOKS-AND-APIS.md`, `STANDARDS-REUSE.md`, `ROADMAP.md`.
