<?php
namespace NguuLai\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Xử lý tác vụ khi hủy kích hoạt plugin.
 */
class Deactivator {

    public static function deactivate(): void {
        // Xóa transients tạm thời nếu có
        delete_transient( 'nguu_lai_analytics_cache' );
    }
}
