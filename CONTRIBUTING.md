# Hướng dẫn Đóng góp & Quy chuẩn Lập trình (CONTRIBUTING.md)

Chào mừng bạn tham gia phát triển plugin **Nguu Lai**! Tài liệu này quy định các chuẩn mực lập trình và quy trình làm việc của dự án.

---

## 📜 1. Chuẩn mực Lập trình (Coding Standards)

Dự án tuân thủ nghiêm ngặt **WordPress Coding Standards (WPCS)** và **PSR-4 / PSR-12**.

### 1.1. Quy ước đặt tên (Naming Conventions)
- **Class:** PascalCase (Ví dụ: `class SettingsPage {}`, `class RestController {}`).
- **File chứa Class:** PascalCase hoặc `class-[kebab-case].php` tùy theo quy ước (Khuyến nghị PascalCase với PSR-4: `SettingsPage.php`).
- **Method & Function:** camelCase hoặc snake_case thống nhất (Chuẩn WP: `snake_case()`, Chuẩn OOP/PSR-12: `camelCase()`). Trong dự án này ưu tiên **snake_case()** khi nối hook WordPress và **camelCase()** cho internal class methods.
- **Variable:** `$snake_case` (Ví dụ: `$user_id`, `$setting_options`).
- **Constants:** UPPER_SNAKE_CASE (Ví dụ: `NGUU_LAI_VERSION`, `NGUU_LAI_PLUGIN_DIR`).
- **Hook Names:** `nguu_lai_action_name`, `nguu_lai_filter_name`.
- **Text Domain:** `nguu-lai` (đồng nhất trong toàn bộ code).

### 1.2. Format & Indentation
- Sử dụng **Tabs** cho thụt đầu dòng (Indentation) theo chuẩn WordPress (hoặc cấu hình trong `.editorconfig`).
- Luôn đặt khoảng trắng bên trong dấu ngoặc:
  ```php
  // ĐÚNG
  if ( is_array( $data ) && ! empty( $data ) ) {
      do_something( $data );
  }

  // SAI
  if(is_array($data)&&!empty($data)){
      do_something($data);
  }
  ```
- Luôn dùng toán tử so sánh nghiêm ngặt (`===`, `!==`). Sử dụng Yoda conditions theo chuẩn WP:
  ```php
  if ( 'active' === $status ) { ... }
  ```

---

## 🛠️ 2. Công cụ Hỗ trợ Kiểm tra Tự động

### 2.1. PHP_CodeSniffer & WPCS
Kiểm tra code có vi phạm chuẩn WordPress hay không:
```bash
composer run lint
```
Tự động định dạng lại code:
```bash
composer run format
```

### 2.2. Phân tích tĩnh (PHPStan)
Kiểm tra type safety và potential bugs ở level cao:
```bash
composer run phpstan
```

### 2.3. Unit Testing & Integration Testing
Chạy toàn bộ bộ test:
```bash
composer run test
```

---

## 🌿 3. Quy trình Git & Nhánh (Branching Strategy)

### 3.1. Phân nhánh (Git Flow)
- `main` / `master`: Nhánh Production (chỉ chứa mã nguồn ổn định, đã release).
- `develop`: Nhánh tích hợp các tính năng mới.
- `feature/<ten-tinh-nang>`: Nhánh phát triển tính năng mới tách từ `develop`.
- `fix/<ten-loi>`: Nhánh sửa lỗi tách từ `develop`.
- `hotfix/<ten-loi-gap>`: Nhánh sửa lỗi khẩn cấp trên `main`.

### 3.2. Quy chuẩn Commit (Conventional Commits)
Thông điệp commit phải tuân theo cấu trúc:
```text
<type>(<scope>): <mô tả ngắn gọn>

[Nội dung chi tiết nếu cần]

[Liên kết issue/task nếu có]
```

**Các type hợp lệ:**
- `feat`: Thêm tính năng mới
- `fix`: Sửa lỗi
- `docs`: Cập nhật tài liệu (README, ARCHITECTURE, ...)
- `style`: Định dạng code (whitespace, format, không ảnh hưởng logic)
- `refactor`: Tái cấu trúc code (không thêm tính năng hay sửa lỗi)
- `test`: Thêm hoặc chỉnh sửa test
- `chore`: Cập nhật cấu hình build, dependencies (composer, npm)

*Ví dụ:*
```text
feat(settings): add toggle for auto caching feature
fix(rest-api): fix permission check on export endpoint
docs(readme): update installation requirements
```

---

## 🚀 4. Quy trình gửi Pull Request (PR)

1. Tạo nhánh mới từ `develop` (`git checkout -b feature/awesome-feature`).
2. Thực hiện code và commit theo quy chuẩn.
3. Chạy `composer run lint` và `composer run test` để đảm bảo không có lỗi.
4. Push nhánh lên remote repository: `git push origin feature/awesome-feature`.
5. Tạo Pull Request trên GitHub hướng vào nhánh `develop`.
6. Điền đầy đủ mô tả theo PR Template:
   - Mô tả thay đổi.
   - Các bước kiểm thử thủ công (Test instructions).
   - Ảnh chụp màn hình / video nếu có thay đổi giao diện.
7. Chờ ít nhất 1 code reviewer duyệt và CI/CD pass trước khi merge.
