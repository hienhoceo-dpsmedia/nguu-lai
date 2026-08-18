# Nguu Lai — WordPress Meme Generator Plugin

> Plugin WordPress chuyên nghiệp tạo Meme Generator tương tác qua Shortcode `[nguu_lai_meme]`, hỗ trợ 16 mẫu Niu Lai chuẩn, tải ảnh tùy chỉnh, chỉnh sửa thời gian thực (Live Canvas 900x900), tích hợp đăng nhập Google (Google One-Tap / OAuth) và quản lý hạn mức theo tiêu chuẩn cao cấp.

---

## 📋 Mục lục

- [Tổng quan & Tính năng](#-tổng-quan--tính-năng)
- [Cách sử dụng Shortcode](#-cách-sử-dụng-shortcode)
- [Giao diện & Thành phần Meme Workbench](#-giao-diện--thành-phần-meme-workbench)
- [Cơ chế Đăng nhập Google & Phân quyền](#-cơ-chế-đăng-nhập-google--phân-quyền)
- [Cấu trúc Thư mục](#-cấu-trúc-thư-mục)
- [Tài liệu Kỹ thuật](#-tài-liệu-kỹ-thuật)
- [Cài đặt & Phát triển](#-cài-đặt--phát-triển)

---

## 🚀 Tổng quan & Tính năng

Plugin **Nguu Lai** mang đến trình tạo meme tương tác chất lượng cao lấy cảm hứng từ [niulai.wiki](https://niulai.wiki/en/):

- 🎨 **Live Meme Workbench (Canvas 900 × 900):** Xem trước trực tiếp mượt mà 60fps, hỗ trợ căn chỉnh kích thước chữ (Text size), vị trí ngang (X) / dọc (Y), bật/tắt watermark.
- 🐮 **16 Template Niu Lai tích hợp:** Thư viện 16 hình ảnh phôi meme sắc nét được tối ưu định dạng `.webp`.
- 📤 **Tải ảnh cá nhân (Upload Custom Image):** Người dùng có thể tải ảnh bất kỳ từ máy để chế meme trực tiếp trong trình duyệt.
- 💬 **Kho câu thoại thông minh (Phrase Library & Random Line):** Tuyển tập các câu thoại kinh điển ("Huh?", "Be serious.", "Say that again?", "Chinese cows can fly", v.v.) và nút Random đổi thoại tức thì.
- 🔒 **Bảo mật xử lý tại Client (100% In-Browser Privacy):** Ảnh được xử lý thuần bằng HTML5 Canvas / Client-side JS / WebAssembly trong trình duyệt của người dùng, không tải ảnh riêng tư lên máy chủ.
- 🔑 **Google Sign-In & Quota System:** Tích hợp Google One-Tap / Button (kế thừa tiêu chuẩn từ `vidbee-wordpress-hybrid`). Đăng nhập bằng Google để mở khóa tải meme không giới hạn và lưu lịch sử.
- ⚙️ **Trang Quản trị Chuyên nghiệp:** Dashboard theo tabs (Logs, Thống kê Analytics, Quản lý Quota/Blocklist, Cấu hình Google Client ID, Watermark, Danh sách câu thoại).

---

## 🏷️ Cách sử dụng Shortcode

Chèn shortcode sau vào bất kỳ bài viết, trang (Page), widget hoặc theme template nào:

```text
[nguu_lai_meme]
```

### Các thuộc tính tùy biến (Attributes):

| Thuộc tính | Giá trị mặc định | Mô tả |
| :--- | :--- | :--- |
| `default_text` | `"啊？"` | Nội dung chữ hiển thị mặc định ban đầu |
| `watermark` | `"1"` | Bật (`"1"`) hoặc tắt (`"0"`) watermark mặc định |
| `watermark_text` | `"niulai.wiki"` | Nội dung chữ watermark góc phải dưới |
| `require_login` | `"0"` | Yêu cầu bắt buộc đăng nhập Google mới được tải (`"1"` hoặc `"0"`) |
| `theme` | `"dark"` | Giao diện hiển thị (`"dark"`, `"light"` hoặc `"paper"`) |
| `title` | `"Let Niu Lai say it for you."` | Tiêu đề khối Meme Workbench |

*Ví dụ nâng cao:*
```text
[nguu_lai_meme default_text="Không thể tin được!" watermark="1" require_login="0"]
```

---

## 🎨 Giao diện & Thành phần Meme Workbench

Cấu trúc giao diện Meme Editor theo chuẩn thiết kế từ `niulai.wiki`:

1. **Khung Live Preview Canvas:** Canvas 900 × 900 tỉ lệ 1:1, xuất file PNG độ phân giải cao.
2. **Bộ Điều khiển (Controls Panel):**
   - Text Input: Nhập văn bản (tối đa 42 ký tự).
   - Text Size Slider: Kích thước chữ (28px - 120px, mặc định 64px).
   - Horizontal Position (X): Vị trí chữ theo trục ngang (8% - 92%).
   - Vertical Position (Y): Vị trí chữ theo trục dọc (8% - 92%).
   - Checkbox Watermark: Giữ hoặc tắt watermark bản quyền.
   - Upload Button: Chọn ảnh từ máy tính hoặc điện thoại.
   - Action Buttons: `Random line` và `Download meme ↓`.
3. **Thư viện 16 Mẫu Niu Lai (Template Grid):** 16 nút thumbnail tương tác chuyển đổi nhanh phôi ảnh mà vẫn giữ nguyên chữ đang soạn thảo.
4. **Thư viện Câu thoại (Phrase Grid):** Các chip câu thoại nhanh, bấm vào là điền trực tiếp vào ô soạn thảo.

---

## 🔐 Cơ chế Đăng nhập Google & Phân quyền

Kế thừa kiến trúc từ dự án `vidbee-wordpress-hybrid`:

- **Khách vãng lai (Guest):** Cho phép tạo và tải tối đa `X` meme/ngày (cấu hình trong Admin Settings) hoặc hiển thị Modal yêu cầu Đăng nhập Google.
- **Đã đăng nhập Google (Google Logged-in):** Không giới hạn lượt tạo/tải meme (`Unlimited Quota`).
- **Quy trình Xác thực:** Token ID từ Google GIS được gửi về backend REST API `/wp-json/nguu-lai/v1/google-login` để xác thực audience, issuer, exp, và khởi tạo phiên WordPress tự động.

---

## 📁 Cấu trúc Thư mục

```text
nguu-lai/
├── assets/
│   ├── css/
│   │   ├── admin.css          # CSS Dashboard quản trị
│   │   └── meme-workbench.css # CSS Giao diện Meme Editor
│   ├── js/
│   │   ├── admin.js            # JS quản trị Admin Tabs
│   │   ├── google-auth.js      # JS Google One-Tap & Sign-In integration
│   │   └── meme-workbench.js   # JS Canvas Engine & Meme Editor logic
│   └── memes/                  # 16 template phôi gốc Niu Lai
│       ├── niulai_01.webp
│       ├── ...
│       └── niulai_16.webp
├── includes/
│   ├── Admin/                  # Quản trị & Cấu hình Options
│   │   ├── Settings.php
│   │   └── AdminAssets.php
│   ├── Api/                    # REST API Endpoints
│   │   ├── RestController.php  # /google-login, /update-log, /quota
│   │   └── AjaxHandler.php
│   ├── Core/                   # Loader, I18n, Activator, Deactivator
│   │   ├── Activator.php
│   │   ├── Deactivator.php
│   │   ├── I18n.php
│   │   └── Loader.php
│   ├── Frontend/               # Xử lý Shortcode & Public View
│   │   ├── FrontendAssets.php
│   │   └── Shortcode.php       # [nguu_lai_meme] logic
│   └── Models/                 # DB Logs, Analytics & Quota Manager
│       └── Database.php
├── languages/                  # File dịch đa ngôn ngữ i18n
├── templates/                  # Views HTML
│   ├── admin/
│   │   └── settings-page.php
│   └── frontend/
│       └── meme-workbench.php  # Template HTML5 của Meme Maker
├── ARCHITECTURE.md             # Kiến trúc hệ thống
├── CHANGELOG.md                # Lịch sử thay đổi
├── CONTRIBUTING.md             # Tiêu chuẩn code WPCS
├── HOOKS-AND-APIS.md           # Custom Hooks & REST APIs
├── nguu-lai.php                # Bootstrap chính của Plugin
├── README.md                   # Tài liệu này
├── ROADMAP.md                  # Lộ trình & Checklist công việc
├── SECURITY.md                 # Chính sách bảo mật
├── STANDARDS-REUSE.md          # Tiêu chuẩn kế thừa từ VidBee Hybrid
└── uninstall.php               # Dọn dẹp dữ liệu khi gỡ plugin
```

---

## 📚 Tài liệu Kỹ thuật

- 🌟 [Tiêu chuẩn Kế thừa từ VidBee Hybrid (STANDARDS-REUSE.md)](./STANDARDS-REUSE.md) *(Admin Tabs, Google OAuth, Quota, GDPR Analytics, Proxy IP)*
- 🏛️ [Kiến trúc hệ thống (ARCHITECTURE.md)](./ARCHITECTURE.md) *(Meme Canvas Engine, Shortcode Lifecycle, REST APIs)*
- 🪝 [Danh sách Hooks & APIs (HOOKS-AND-APIS.md)](./HOOKS-AND-APIS.md) *(Shortcode filters, action hooks, REST routes)*
- 🛡️ [Chính sách bảo mật (SECURITY.md)](./SECURITY.md)
- 🗺️ [Lộ trình phát triển & Checklist (ROADMAP.md)](./ROADMAP.md)

---

## 📄 Giấy phép

Phát hành dưới giấy phép [MIT / GPLv2+](https://www.gnu.org/licenses/gpl-2.0.html).
