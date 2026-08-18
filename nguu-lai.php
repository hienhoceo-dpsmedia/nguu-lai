<?php
/**
 * Plugin Name:       Ngưu Lại — Trình Tạo Meme & Đăng Nhập Google
 * Plugin URI:        https://dps.media
 * Description:       Trình tạo meme tương tác chất lượng cao qua Shortcode [nguu_lai_meme], tích hợp Canvas 900x900, 16 phôi Niu Lai chuẩn, xử lý an toàn 100% trong trình duyệt và đăng nhập Google 1 chạm.
 * Version:           1.0.0
 * Author:            DPS Media
 * Author URI:        https://dps.media
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       nguu-lai
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Ngăn chặn truy cập file trực tiếp
}

// Định nghĩa các hằng số Plugin
define( 'NGUU_LAI_VERSION', '1.0.0' );
define( 'NGUU_LAI_PLUGIN_FILE', __FILE__ );
define( 'NGUU_LAI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NGUU_LAI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NGUU_LAI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloading cơ bản theo chuẩn PSR-4 cho namespace NguuLai\
 */
spl_autoload_register( function ( $class ) {
    $prefix   = 'NguuLai\\';
    $base_dir = NGUU_LAI_PLUGIN_DIR . 'includes/';

    $len = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

/**
 * Đăng ký hook kích hoạt & hủy kích hoạt
 */
register_activation_hook( __FILE__, function () {
    \NguuLai\Core\Activator::activate();
} );

register_deactivation_hook( __FILE__, function () {
    \NguuLai\Core\Deactivator::deactivate();
} );

/**
 * Khởi chạy toàn bộ hệ thống plugin
 */
function nguu_lai_init_plugin() {
    $plugin = \NguuLai\Core\Plugin::get_instance();
    $plugin->run();
}
add_action( 'plugins_loaded', 'nguu_lai_init_plugin' );
