# Chính sách & Nguyên tắc Bảo mật (SECURITY.md)

Tài liệu này là cẩm nang bắt buộc về các tiêu chuẩn và kỹ thuật bảo mật khi phát triển plugin **Nguu Lai**.

---

## 🛡️ 1. Nguyên tắc cốt lõi (Core Principles)

1. **Never Trust User Input:** Luôn lọc và chuẩn hóa toàn bộ dữ liệu đầu vào (GET, POST, COOKIE, REST Payload).
2. **Always Escape Output:** Luôn escape dữ liệu ngay tại thời điểm render ra HTML/JSON/Attribute.
3. **Verify Intent & Authorization:** Luôn kiểm tra Nonce (CSRF) và Capability (Quyền hạn người dùng) trước khi thực hiện hành động.
4. **Prevent Direct Access:** Không cho phép truy cập trực tiếp file `.php` từ trình duyệt.

---

## 🔒 2. Các quy tắc kỹ thuật chi tiết

### 2.1. Ngăn chặn truy cập file trực tiếp (Direct File Access)
Mọi file PHP trong plugin phải có dòng kiểm tra sau ở ngay đầu file:
```php
<?php
/**
 * Prevent direct file access.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

---

### 2.2. Kiểm tra quyền hạn người dùng (Capabilities & Authorization)
Không bao giờ tin tưởng việc người dùng có thể thấy nút bấm hay menu. Luôn kiểm tra quyền trong callback xử lý:

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ), 403 );
}
```

Đối với REST API:
```php
register_rest_route( 'nguu-lai/v1', '/settings', [
    'methods'             => 'POST',
    'callback'            => [ $this, 'save_settings' ],
    'permission_callback' => function () {
        return current_user_can( 'manage_options' );
    },
] );
```

---

### 2.3. Chống tấn công CSRF bằng Nonces

#### Trong Form Admin:
```php
<!-- Tạo nonce field -->
<form method="post" action="">
    <?php wp_nonce_field( 'nguu_lai_save_settings_action', 'nguu_lai_nonce' ); ?>
    <!-- các input khác -->
    <button type="submit" name="nguu_lai_submit">Lưu</button>
</form>
```

#### Xác thực khi submit form:
```php
if ( isset( $_POST['nguu_lai_submit'] ) ) {
    if ( ! isset( $_POST['nguu_lai_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nguu_lai_nonce'] ) ), 'nguu_lai_save_settings_action' ) ) {
        wp_die( esc_html__( 'Phiên làm việc hết hạn hoặc không hợp lệ.', 'nguu-lai' ), 403 );
    }
    // Xử lý tiếp...
}
```

#### Trong AJAX:
```php
check_ajax_referer( 'nguu_lai_ajax_action', 'security' );
```

---

### 2.4. Khử độc dữ liệu đầu vào (Input Sanitization)
Sử dụng các hàm chuyên biệt của WordPress tùy theo kiểu dữ liệu:

| Kiểu dữ liệu | Hàm Sanitization tương ứng |
| :--- | :--- |
| Chuỗi văn bản đơn dòng | `sanitize_text_field( $str )` |
| Đoạn văn bản nhiều dòng | `sanitize_textarea_field( $str )` |
| Địa chỉ Email | `sanitize_email( $email )` |
| Đường dẫn URL | `esc_url_raw( $url )` |
| Tên file / Slug | `sanitize_file_name( $name )`, `sanitize_title( $slug )` |
| Số nguyên dương | `absint( $num )` |
| Số nguyên / Số thực | `intval( $num )`, `floatval( $num )` |
| Mã HTML an toàn | `wp_kses_post( $html )` hoặc `wp_kses( $html, $allowed_tags )` |

```php
// Luôn unslash trước khi sanitize nếu lấy từ superglobals ($_GET, $_POST)
$user_name = isset( $_POST['user_name'] ) ? sanitize_text_field( wp_unslash( $_POST['user_name'] ) ) : '';
```

---

### 2.5. Escape dữ liệu đầu ra (Output Escaping)
Áp dụng quy tắc "Late Escaping" (Escape sát thời điểm in ra):

| Ngữ cảnh xuất | Hàm Escaping tương ứng |
| :--- | :--- |
| Văn bản HTML thông thường | `esc_html( $text )` |
| Giá trị của HTML attribute | `esc_attr( $attribute )` |
| Đường dẫn URL trong href, src | `esc_url( $url )` |
| Nội dung thẻ `<textarea>` | `esc_textarea( $text )` |
| Dữ liệu JSON trong inline script | `wp_json_encode( $data )` |
| Chuỗi có chứa thẻ HTML cho phép | `wp_kses_post( $html )` |

```php
<!-- Ví dụ đúng -->
<a href="<?php echo esc_url( $item_url ); ?>" title="<?php echo esc_attr( $item_title ); ?>">
    <?php echo esc_html( $item_text ); ?>
</a>
```

---

### 2.6. Chống SQL Injection
Tuyệt đối **KHÔNG** nối chuỗi vào câu lệnh SQL. Luôn sử dụng `$wpdb->prepare()`:

```php
global $wpdb;
$table_name = $wpdb->prefix . 'nguu_lai_logs';

// ĐÚNG: Sử dụng prepare()
$result = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$table_name} WHERE user_id = %d AND action = %s LIMIT %d",
        $user_id,
        $action_name,
        $limit
    )
);

// HOẶC sử dụng các method an toàn có sẵn của $wpdb:
$wpdb->insert(
    $table_name,
    [ 'user_id' => $user_id, 'action' => $action_name ],
    [ '%d', '%s' ]
);
```

---

### 2.7. Xử lý File Upload an toàn
1. Luôn kiểm tra MIME type và extension cho phép thông qua `wp_check_filetype()`.
2. Sử dụng `wp_handle_upload()` thay vì tự di chuyển file bằng `move_uploaded_file()`.
3. Giới hạn dung lượng tối đa cho phép.

---

## 🚨 3. Báo cáo lỗ hổng bảo mật (Vulnerability Disclosure)

Nếu phát hiện bất kỳ lỗ hổng bảo mật nào trong dự án, vui lòng tuân thủ quy trình Tiết lộ có trách nhiệm (Responsible Disclosure):
- **Không tạo Public Issue** trên GitHub.
- Gửi thông tin chi tiết về lỗi (kèm PoC nếu có) tới email: `security@yourdomain.com`.
- Đội ngũ bảo mật sẽ phản hồi và xử lý trong vòng 48 giờ.
