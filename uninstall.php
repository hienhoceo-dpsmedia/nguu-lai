<?php
/**
 * Logic dọn dẹp khi gỡ bỏ cài đặt Plugin Ngưu Lai.
 *
 * @package NguuLai
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Xóa bảng nhật ký nếu có tùy chọn cho phép xóa sạch
global $wpdb;
$table_logs = $wpdb->prefix . 'nguu_lai_logs';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
$wpdb->query( "DROP TABLE IF EXISTS {$table_logs}" );

// Xóa toàn bộ các Options của plugin
$options = [
    'nguu_lai_version',
    'nguu_lai_google_client_id',
    'nguu_lai_require_login',
    'nguu_lai_guest_quota',
    'nguu_lai_watermark_text',
    'nguu_lai_watermark_enabled',
    'nguu_lai_default_phrases',
    'nguu_lai_blocked_ips',
    'nguu_lai_trust_proxies',
];

foreach ( $options as $option ) {
    delete_option( $option );
}
