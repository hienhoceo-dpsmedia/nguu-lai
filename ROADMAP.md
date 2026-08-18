# Kế hoạch Phát triển & Lộ trình (ROADMAP.md)

Tài liệu này theo dõi tiến độ phát triển, lộ trình các phiên bản và danh sách các hạng mục công việc (Checklist) của dự án plugin **Nguu Lai** (Meme Generator & Google Login).

---

## 🎯 Mục tiêu Dự án (Project Goals)

- [x] Xây dựng plugin tạo Meme tương tác thông qua Shortcode `[nguu_lai_meme]`.
- [x] Cung cấp giao diện Meme Workbench Canvas 900x900 mượt mà, tối ưu UX, hỗ trợ 16 phôi Niu Lai + Tải ảnh cá nhân.
- [x] Tích hợp Đăng nhập Google (Google One-Tap / OAuth Button) và hệ thống phân quyền/quota như đã chuẩn hóa trong `vidbee-wordpress-hybrid`.
- [x] Xử lý 100% Client-side trong trình duyệt của người dùng để bảo vệ quyền riêng tư và tiết kiệm tài nguyên máy chủ.
- [x] Toàn bộ giao diện người dùng (Frontend & Admin) được chuẩn hóa **100% Thuần Việt**.

---

## 📋 Danh sách Công việc Chi tiết (Task Checklist)

### 📌 Giai đoạn 1: Chuẩn bị Tài liệu & Tài nguyên (Foundation & Assets)
- [x] Soạn thảo bộ tài liệu kỹ thuật hoàn chỉnh (`README.md`, `ARCHITECTURE.md`, `SECURITY.md`, `CONTRIBUTING.md`, `HOOKS-AND-APIS.md`, `STANDARDS-REUSE.md`, `ROADMAP.md`, `CHANGELOG.md`).
- [x] Tải và đóng gói 16 phôi ảnh WebP Niu Lai vào `assets/memes/niulai_01.webp` đến `niulai_16.webp`.
- [x] Thiết lập `composer.json` cho PSR-4 autoloading và cấu hình `.gitignore`, `.editorconfig`.
- [x] Khởi tạo file bootstrap chính `nguu-lai.php` và `uninstall.php`.

---

### 🎨 Giai đoạn 2: Phát triển Meme Workbench Frontend (UI & Canvas Engine)
- [x] Xây dựng Template view `templates/frontend/meme-workbench.php` 100% thuần Việt.
- [x] Viết CSS `assets/css/meme-workbench.css` (Dark theme, canvas shadow, template grid, phrase chips).
- [x] Xây dựng JS `assets/js/meme-workbench.js`:
  - [x] Canvas 2D render loop (900x900).
  - [x] Tự động ngắt dòng thông minh (Smart text wrap cho tiếng Việt có dấu).
  - [x] Điều khiển kích thước (Size), vị trí ngang (X) và dọc (Y).
  - [x] Bật/tắt watermark `niulai.wiki`.
  - [x] Tải ảnh cá nhân từ máy tính/điện thoại qua `URL.createObjectURL`.
  - [x] Lưới 16 template chuyển đổi nhanh phôi ảnh giữ nguyên text.
  - [x] Lưới Phrase Library & Nút Random line với 16 câu thoại tiếng Việt đặc sắc.
  - [x] Xuất ảnh PNG tải về trực tiếp (`canvas.toBlob`).
- [x] Đăng ký shortcode `[nguu_lai_meme]` trong `includes/Frontend/Shortcode.php`.

---

### 🔑 Giai đoạn 3: Tích hợp Google Login & Quota System (Auth & Quotas)
- [x] Tích hợp Google Identity Services SDK (`assets/js/google-auth.js`).
- [x] Xây dựng endpoint REST API `/wp-json/nguu-lai/v1/google-login` xác thực ID token qua Google Tokeninfo API.
- [x] Tạo/khớp tài khoản WordPress và cấp phiên đăng nhập (`wp_set_auth_cookie`).
- [x] Quản lý Quota: Giới hạn lượt tải miễn phí cho khách (Guest Quota) và mở khóa không giới hạn khi đăng nhập Google.
- [x] Popup / Modal thông báo đăng nhập khi người dùng đạt giới hạn.

---

### ⚙️ Giai đoạn 4: Quản trị Admin Dashboard (Settings, Logs & Analytics)
- [x] Xây dựng Admin Settings Page với 5 tabs 100% thuần Việt (Tổng quan, Cài đặt Google & Hạn mức, Quản lý Mẫu & Câu thoại, Nhật ký & Thống kê, Bảo mật & Chặn IP).
- [x] Bảng cơ sở dữ liệu `wp_nguu_lai_logs` lưu trữ lịch sử tạo meme ẩn danh / user ID.
- [x] Bộ lọc ngày tháng (Hôm nay, Hôm qua, 7 ngày qua, 30 ngày qua, Tùy chỉnh).
- [x] Thống kê Analytics (Top template được sử dụng, tổng lượt tải, số lượng người dùng).
- [x] Tính năng Dọn dẹp logs và Chặn IP lạm dụng.

---

### 🧪 Giai đoạn 5: Kiểm thử, Tối ưu & Đóng gói (Testing & Release)
- [x] Kiểm tra cú pháp toàn bộ file PHP (`php -l`) đạt 100% không lỗi.
- [x] Kiểm tra Responsive trên Mobile / Tablet / Desktop.
- [x] Kiểm tra tính bảo mật (Nonce, Capability, Sanitization, Escaping, Prepared SQL).
- [x] Phát hành phiên bản `v1.0.0`.
