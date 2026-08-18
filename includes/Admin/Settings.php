<?php
namespace NguuLai\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NguuLai\Models\Database;

/**
 * Quản lý Menu, Form xử lý Admin POST và Render Giao diện Cài đặt.
 */
class Settings {

    public function register_admin_menu(): void {
        add_menu_page(
            'Ngưu Lai Meme',
            'Ngưu Lai Meme',
            'manage_options',
            'nguu-lai-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-format-image',
            65
        );
    }

    public function register_post_handlers(): void {
        add_action( 'admin_post_nguu_lai_save_settings', [ $this, 'handle_save_settings' ] );
        add_action( 'admin_post_nguu_lai_save_phrases', [ $this, 'handle_save_phrases' ] );
        add_action( 'admin_post_nguu_lai_block_ip', [ $this, 'handle_block_ip' ] );
        add_action( 'admin_post_nguu_lai_unblock_ip', [ $this, 'handle_unblock_ip' ] );
        add_action( 'admin_post_nguu_lai_clear_blocklist', [ $this, 'handle_clear_blocklist' ] );
        add_action( 'admin_post_nguu_lai_clear_logs', [ $this, 'handle_clear_logs' ] );
    }

    /**
     * Lưu Cài đặt Chung & Google Auth.
     */
    public function handle_save_settings(): void {
        check_admin_referer( 'nguu_lai_save_settings_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $google_client_id  = sanitize_text_field( wp_unslash( $_POST['nguu_lai_google_client_id'] ?? '' ) );
        $require_login     = isset( $_POST['nguu_lai_require_login'] ) ? '1' : '0';
        $guest_quota       = max( 0, intval( $_POST['nguu_lai_guest_quota'] ?? 5 ) );
        $watermark_text    = sanitize_text_field( wp_unslash( $_POST['nguu_lai_watermark_text'] ?? 'niulai.wiki' ) );
        $watermark_enabled = isset( $_POST['nguu_lai_watermark_enabled'] ) ? '1' : '0';
        $trust_proxies     = isset( $_POST['nguu_lai_trust_proxies'] ) ? '1' : '0';

        update_option( 'nguu_lai_google_client_id', $google_client_id );
        update_option( 'nguu_lai_require_login', $require_login );
        update_option( 'nguu_lai_guest_quota', $guest_quota );
        update_option( 'nguu_lai_watermark_text', $watermark_text );
        update_option( 'nguu_lai_watermark_enabled', $watermark_enabled );
        update_option( 'nguu_lai_trust_proxies', $trust_proxies );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'settings',
            'status' => 'saved',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Lưu Danh sách 16 Câu thoại Gợi ý.
     */
    public function handle_save_phrases(): void {
        check_admin_referer( 'nguu_lai_save_phrases_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $raw_phrases = isset( $_POST['nguu_lai_phrases'] ) ? (array) $_POST['nguu_lai_phrases'] : [];
        $clean_phrases = [];

        foreach ( $raw_phrases as $phrase ) {
            $cleaned = trim( sanitize_text_field( wp_unslash( $phrase ) ) );
            if ( ! empty( $cleaned ) ) {
                $clean_phrases[] = $cleaned;
            }
        }

        if ( empty( $clean_phrases ) ) {
            $clean_phrases = [ 'Hả?', 'Nghiêm túc đi bạn ơi.' ];
        }

        update_option( 'nguu_lai_default_phrases', $clean_phrases );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'phrases',
            'status' => 'phrases_saved',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Thêm IP vào danh sách chặn.
     */
    public function handle_block_ip(): void {
        check_admin_referer( 'nguu_lai_block_ip_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $ip = sanitize_text_field( wp_unslash( $_POST['block_ip'] ?? '' ) );
        if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
            $blocked   = get_option( 'nguu_lai_blocked_ips', [] );
            $blocked[] = [
                'ip'         => $ip,
                'reason'     => sanitize_text_field( wp_unslash( $_POST['block_reason'] ?? 'Chặn thủ công bởi Quản trị viên' ) ),
                'created_at' => current_time( 'mysql' ),
                'expires_at' => 0, // Vĩnh viễn trừ khi mở khóa
            ];
            update_option( 'nguu_lai_blocked_ips', $blocked );
        }

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'security',
            'status' => 'ip_blocked',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Mở chặn 1 IP.
     */
    public function handle_unblock_ip(): void {
        check_admin_referer( 'nguu_lai_unblock_ip_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $ip      = sanitize_text_field( wp_unslash( $_GET['ip'] ?? '' ) );
        $blocked = get_option( 'nguu_lai_blocked_ips', [] );
        $new_list = array_values( array_filter( $blocked, function ( $item ) use ( $ip ) {
            return ( $item['ip'] ?? '' ) !== $ip;
        } ) );

        update_option( 'nguu_lai_blocked_ips', $new_list );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'security',
            'status' => 'ip_unblocked',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Xóa toàn bộ danh sách chặn IP.
     */
    public function handle_clear_blocklist(): void {
        check_admin_referer( 'nguu_lai_clear_blocklist_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        update_option( 'nguu_lai_blocked_ips', [] );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'security',
            'status' => 'blocklist_cleared',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Xóa toàn bộ nhật ký Logs.
     */
    public function handle_clear_logs(): void {
        check_admin_referer( 'nguu_lai_clear_logs_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        global $wpdb;
        $table = Database::get_table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query( "TRUNCATE TABLE {$table}" );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'logs',
            'status' => 'logs_cleared',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Render trang Admin Settings với các Tabs thuần Việt.
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền truy cập trang này.', 'nguu-lai' ) );
        }

        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'overview';
        $status      = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';

        // Dữ liệu bộ lọc logs
        $preset     = isset( $_GET['preset'] ) ? sanitize_key( $_GET['preset'] ) : '';
        $start_date = isset( $_GET['start_date'] ) ? sanitize_text_field( wp_unslash( $_GET['start_date'] ) ) : '';
        $end_date   = isset( $_GET['end_date'] ) ? sanitize_text_field( wp_unslash( $_GET['end_date'] ) ) : '';
        $search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

        $today_str = current_time( 'Y-m-d' );
        if ( 'today' === $preset || '1' === $preset ) {
            $start_date = $today_str;
            $end_date   = $today_str;
        } elseif ( 'yesterday' === $preset ) {
            $start_date = date( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) );
            $end_date   = $start_date;
        } elseif ( '7' === $preset ) {
            $start_date = date( 'Y-m-d', strtotime( '-7 days', current_time( 'timestamp' ) ) );
            $end_date   = $today_str;
        } elseif ( '30' === $preset ) {
            $start_date = date( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) );
            $end_date   = $today_str;
        }

        $per_page = 25;
        $paged    = max( 1, intval( $_GET['paged'] ?? 1 ) );
        $offset   = ( $paged - 1 ) * $per_page;

        $logs       = Database::get_logs( $per_page, $offset, $start_date, $end_date, $search );
        $total_logs = Database::count_logs( $start_date, $end_date, $search );
        $total_pages = ceil( $total_logs / $per_page );
        $analytics  = Database::get_analytics( $start_date, $end_date );

        // Danh sách IP chặn
        $blocked_ips = get_option( 'nguu_lai_blocked_ips', [] );

        // Các options
        $google_client_id  = get_option( 'nguu_lai_google_client_id', '' );
        $require_login     = (bool) get_option( 'nguu_lai_require_login', false );
        $guest_quota       = get_option( 'nguu_lai_guest_quota', 5 );
        $watermark_text    = get_option( 'nguu_lai_watermark_text', 'niulai.wiki' );
        $watermark_enabled = (bool) get_option( 'nguu_lai_watermark_enabled', '1' );
        $trust_proxies     = (bool) get_option( 'nguu_lai_trust_proxies', '1' );
        $phrases           = get_option( 'nguu_lai_default_phrases', [] );

        // Render view template
        include NGUU_LAI_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }
}
