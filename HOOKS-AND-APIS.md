# Danh sách Hooks & APIs (HOOKS-AND-APIS.md)

Tài liệu này cung cấp đầy đủ thông tin về **Shortcode**, **Filter Hooks**, **Action Hooks** và **REST API Endpoints** của plugin **Nguu Lai**.

---

## 🏷️ 1. Shortcode `[nguu_lai_meme]`

Hiển thị giao diện Meme Workbench tương tác trên trang hoặc bài viết.

### Cú pháp cơ bản:
```text
[nguu_lai_meme]
```

### Bảng thuộc tính (Shortcode Attributes):
| Thuộc tính | Kiểu dữ liệu | Giá trị mặc định | Mô tả |
| :--- | :--- | :--- | :--- |
| `default_text` | `string` | `"啊？"` | Văn bản hiển thị ban đầu trên Canvas |
| `default_template` | `int` | `1` | Số thứ tự template ban đầu (1 đến 16) |
| `watermark` | `string` | `"1"` | Bật (`"1"`) hoặc Tắt (`"0"`) checkbox watermark |
| `watermark_text` | `string` | `"niulai.wiki"` | Nội dung chữ in watermark |
| `require_login` | `string` | `"0"` | Bắt buộc đăng nhập Google mới cho tải (`"1"` / `"0"`) |
| `theme` | `string` | `"dark"` | Giao diện màu (`"dark"`, `"light"`, `"paper"`) |
| `title` | `string` | `"Let Niu Lai say it for you."` | Tiêu đề khối Meme |
| `aside` | `string` | `"Pick a face, add one line..."` | Đoạn mô tả phụ dưới tiêu đề |

*Ví dụ:*
```text
[nguu_lai_meme default_text="Thật không thể tin được!" watermark="1" require_login="1"]
```

---

## 🪝 2. Filter Hooks

### 2.1. `nguu_lai_meme_phrases`
Tùy biến danh sách các câu thoại mẫu trong Phrase Library.

- **Vị trí:** `includes/Frontend/Shortcode.php`
- **Tham số:** `$phrases` *(array)*: Danh sách mảng các câu thoại chuỗi.
- **Ví dụ:**
```php
add_filter( 'nguu_lai_meme_phrases', function ( array $phrases ) {
    $phrases[] = 'Thật là bất ngờ!';
    $phrases[] = 'Chuyện gì đang xảy ra thế này?';
    $phrases[] = 'Để tôi suy nghĩ lại đã.';
    return $phrases;
} );
```

---

### 2.2. `nguu_lai_meme_templates`
Cho phép theme hoặc plugin khác thêm các phôi ảnh meme tùy chỉnh vào lưới 16 template mặc định.

- **Vị trí:** `includes/Frontend/Shortcode.php`
- **Tham số:** `$templates` *(array)*: Danh sách URL ảnh phôi.
- **Ví dụ:**
```php
add_filter( 'nguu_lai_meme_templates', function ( array $templates ) {
    $templates[] = get_stylesheet_directory_uri() . '/assets/custom-meme-1.webp';
    return $templates;
} );
```

---

### 2.3. `nguu_lai_canvas_config`
Tùy biến các thông số canvas mặc định (font chữ, màu sắc viền, kích thước tối đa).

- **Vị trí:** `includes/Frontend/FrontendAssets.php`
- **Tham số:** `$config` *(array)*: Mảng cấu hình truyền sang JS `nguuLaiConfig`.
- **Ví dụ:**
```php
add_filter( 'nguu_lai_canvas_config', function ( array $config ) {
    $config['canvas_width']  = 1080;
    $config['canvas_height'] = 1080;
    $config['default_font']  = 'Arial, "Noto Sans", sans-serif';
    return $config;
} );
```

---

## ⚡ 3. Action Hooks

### 3.1. `nguu_lai_meme_downloaded`
Kích hoạt khi người dùng hoàn tất tải xuống một meme (gửi tín hiệu về server).

- **Vị trí:** `includes/Api/RestController.php`
- **Tham số:**
  - `$log_id` *(int)*: ID bản ghi nhật ký.
  - `$payload` *(array)*: Chi tiết gồm `user_id`, `template`, `text`, `ip_address`.
- **Ví dụ:**
```php
add_action( 'nguu_lai_meme_downloaded', function ( int $log_id, array $payload ) {
    // Tích hợp điểm thưởng Gamification, gửi thông báo hoặc phân tích
    if ( $payload['user_id'] > 0 ) {
        // Tặng điểm cho user
    }
}, 10, 2 );
```

---

## 🌐 4. REST API Endpoints

Namespace gốc: `/wp-json/nguu-lai/v1/`

| Phương thức | Endpoint | Mô tả | Quyền hạn |
| :--- | :--- | :--- | :--- |
| `POST` | `/google-login` | Xác thực Google ID Token & tạo phiên làm việc WP | Public (Kèm WP Nonce) |
| `GET` | `/quota` | Lấy số lượt tải meme còn lại của IP/User hiện tại | Public |
| `POST` | `/update-log` | Ghi nhận sự kiện tạo/tải meme về server | Public |
| `GET` | `/settings` | Lấy cấu hình plugin | `manage_options` |
| `POST` | `/settings` | Cập nhật cấu hình | `manage_options` |
| `GET` | `/export-logs` | Xuất nhật ký tạo meme ra CSV/JSON | `manage_options` |

---

### 4.1. Endpoint: Google Login (`POST /google-login`)
- **Headers:** `Content-Type: application/json`, `X-WP-Nonce: <wp_rest_nonce>`
- **Payload:**
```json
{
  "credential": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
}
```
- **Response thành công (200 OK):**
```json
{
  "success": true,
  "message": "Đăng nhập Google thành công!",
  "data": {
    "user_id": 15,
    "name": "Hien Ho",
    "email": "hienho@example.com",
    "quota": "unlimited",
    "is_logged_in": true
  }
}
```

---

### 4.2. Endpoint: Kiểm tra Quota (`GET /quota`)
- **Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "is_logged_in": false,
    "remaining_quota": 3,
    "daily_limit": 5,
    "require_login": false
  }
}
```

---

### 4.3. Endpoint: Ghi nhận Tải Meme (`POST /update-log`)
- **Payload:**
```json
{
  "template": "niulai_02.webp",
  "text": "Thật không thể tin được!",
  "watermark_applied": true,
  "session_id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d"
}
```
- **Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "log_id": 2048,
    "remaining_quota": 2
  }
}
```
