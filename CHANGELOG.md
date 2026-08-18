# Nhật ký Thay đổi (CHANGELOG.md)

Tất cả các thay đổi đáng chú ý của dự án **Ngưu Lai** sẽ được ghi lại trong tài liệu này.

Định dạng tài liệu tuân theo chuẩn [Keep a Changelog](https://keepachangelog.com/vi/1.0.0/), và dự án áp dụng quy tắc [Semantic Versioning (SemVer)](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-08-18

### Đã thêm (Added)
- **Giao diện Meme Workbench 100% Thuần Việt:**
  - Khung Canvas 2D độ phân giải cao 900 × 900 xuất ảnh PNG sắc nét.
  - Tự động ngắt dòng thông minh (Smart text wrap) cho tiếng Việt có dấu.
  - Thanh trượt kích thước chữ (28px - 120px), vị trí ngang X (8% - 92%), vị trí dọc Y (8% - 92%).
  - 16 phôi ảnh WebP Niu Lai nguyên bản đóng gói sẵn trong `assets/memes/`.
  - Hỗ trợ tải ảnh cá nhân từ máy tính / điện thoại (`URL.createObjectURL`).
  - Kho 16 câu thoại gợi ý tiếng Việt đặc sắc + nút Đổi câu ngẫu nhiên 🎲.
  - Bật/tắt watermark bản quyền.
  - Toàn bộ quá trình tạo ảnh được xử lý 100% Client-side trong trình duyệt (không tải ảnh người dùng lên máy chủ).
- **Tích hợp Đăng nhập Google (Google One-Tap & OAuth Button):**
  - Xác thực Google ID Token phía Backend thông qua endpoint `/wp-json/nguu-lai/v1/google-login`.
  - Tự động tạo tài khoản WordPress và duy trì phiên đăng nhập an toàn (`wp_set_auth_cookie`).
  - Hệ thống Quota: Khách vãng lai bị giới hạn lượt tải mỗi ngày; Đăng nhập Google để mở khóa tải không giới hạn.
  - Popup / Modal Google Sign-In thông minh khi hết lượt tải miễn phí.
- **Trang Quản trị Admin Dashboard 100% Thuần Việt:**
  - 5 Tabs điều hướng: *Tổng quan & Shortcode*, *Cài đặt Google & Hạn mức*, *Quản lý Câu thoại & Mẫu*, *Nhật ký & Thống kê*, *Bảo mật & Chặn IP*.
  - Thẻ KPI thống kê: Tổng số meme đã tạo, Lượng IP truy cập, Người dùng Google.
  - Bảng xếp hạng Top mẫu phôi được dùng nhiều nhất.
  - Bộ lọc nhật ký theo mốc thời gian (*Hôm nay, Hôm qua, 7 ngày, 30 ngày, Tùy chỉnh*) kèm phân trang.
  - Quản lý danh sách IP bị chặn và tin cậy Proxy/Cloudflare.
- **Cấu trúc & Tài liệu:**
  - Chuẩn OOP PSR-4 autoloading với Composer.
  - Toàn bộ tài liệu kỹ thuật: `README.md`, `ARCHITECTURE.md`, `SECURITY.md`, `CONTRIBUTING.md`, `HOOKS-AND-APIS.md`, `STANDARDS-REUSE.md`, `ROADMAP.md`.
