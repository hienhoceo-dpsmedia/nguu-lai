<?php
namespace NguuLai\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Quản lý Enqueue Assets cho trang Quản trị Admin.
 */
class AdminAssets {

    public function enqueue_assets( string $hook_suffix ): void {
        // Chỉ nạp trên trang cấu hình của Ngưu Lại
        if ( false === strpos( $hook_suffix, 'nguu-lai-settings' ) ) {
            return;
        }

        wp_enqueue_style(
            'nguu-lai-admin-css',
            NGUU_LAI_PLUGIN_URL . 'assets/css/admin.css',
            [],
            NGUU_LAI_VERSION
        );

        wp_enqueue_script(
            'nguu-lai-admin-js',
            NGUU_LAI_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            NGUU_LAI_VERSION,
            true
        );

        wp_localize_script( 'nguu-lai-admin-js', 'nguuLaiAdmin', [
            'copied_text' => 'Đã sao chép mã shortcode vào bộ nhớ tạm! 📋',
            'confirm_clear_logs' => 'Bạn có chắc chắn muốn xóa toàn bộ nhật ký tạo meme không?',
            'confirm_clear_blocklist' => 'Bạn có chắc muốn mở khóa toàn bộ IP trong danh sách chặn?',
        ] );
    }
}
