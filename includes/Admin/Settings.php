<?php
namespace NguuLai\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NguuLai\Models\Database;

/**
 * Quản lý Menu, Trang Cài Đặt và Xử lý POST trong WP Admin.
 */
class Settings {

    public static function get_default_viral_phrases(): array {
        return [
            'Tau mà tổn thương thì tbây phải tổn thất',
            'Thấy tui cười đừng nghĩ tui vui, mà thấy tui khóc đừng nghĩ tui buồn.',
            'Cái đầu của tui tỉnh như tỉnh uỷ.',
            'Thấy chuyện bất bình, chụp màn hình gửi quý dị xem liền.',
            'Cái đồ không có nổi 1000 tỷ.',
            'Chúng ta nghèo không đáng sợ, mà chỉ sợ nghèo đứng thôi.',
            'T bước ra đường như chai dầu thơm bị bể.',
            'Con đàn bà xa xỉ là t.',
            'Thua thì chung đ\' run.',
            'Trên đời này đừng có làm điều gì hết, đã làm sẽ có người biết, đã có người biết là t sẽ biết.',
            'Không nên giao du với những loại tầm thường, vì loại tầm thường sẽ kéo mình đi làm chuyện tầm bậy.',
            'Tại sao? Đứng đây rồi còn hỏi tại sao? Về học lại đi.',
            'Trùm đầu, trùm cuối, trùm giữa đume t trùm mền tbây hết.',
            'Hả?',
            'Ủa alo? Gì vậy trời?',
            'Nghiêm túc đi bạn ơi.',
            'Nói lại lần nữa xem?',
            'Cạn lời luôn á.',
            'Biết nói gì bây giờ?',
            'Đúng rồi, bạn là nhất!',
            'Đỉnh chóp luôn á!',
            'Bò cũng biết bay nha.',
            'Không thể tin được luôn!',
            'Chuẩn bị xem lại lần hai.',
            'Sau cơn mưa trời lại sáng.',
            'Tôi đến, tôi kêu "Ụm bò", rồi tôi đi.',
            'Bình tĩnh lại nào.',
            'Chuyện này có hợp lý không?',
            'Trong mơ cái gì cũng có.',
        ];
    }

    public function register_admin_menu(): void {
        add_menu_page(
            'Ngưu Lai Meme',
            'Ngưu Lai Meme',
            'manage_options',
            'nguu-lai-settings',
            [ $this, 'render_settings_page' ],
            'dashicons-format-image',
            30
        );
    }

    public function register_post_handlers(): void {
        add_action( 'admin_post_nguu_lai_save_settings', [ $this, 'handle_save_settings' ] );
        add_action( 'admin_post_nguu_lai_save_phrases', [ $this, 'handle_save_phrases' ] );
        add_action( 'admin_post_nguu_lai_reset_phrases', [ $this, 'handle_reset_phrases' ] );
        add_action( 'admin_post_nguu_lai_block_ip', [ $this, 'handle_block_ip' ] );
        add_action( 'admin_post_nguu_lai_unblock_ip', [ $this, 'handle_unblock_ip' ] );
        add_action( 'admin_post_nguu_lai_clear_all_logs', [ $this, 'handle_clear_all_logs' ] );
        add_action( 'admin_post_nguu_lai_clear_all_blocks', [ $this, 'handle_clear_all_blocks' ] );
    }

    /**
     * Lưu Cài đặt Chung, Quota & Google Auth.
     */
    public function handle_save_settings(): void {
        check_admin_referer( 'nguu_lai_save_settings_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $google_client_id  = sanitize_text_field( wp_unslash( $_POST['nguu_lai_google_client_id'] ?? '' ) );
        $require_login     = isset( $_POST['nguu_lai_require_login'] ) ? '1' : '0';
        $guest_quota       = max( 0, intval( $_POST['nguu_lai_guest_quota'] ?? 5 ) );
        $watermark_text    = sanitize_text_field( wp_unslash( $_POST['nguu_lai_watermark_text'] ?? 'DPS.MEDIA' ) );
        $watermark_enabled = isset( $_POST['nguu_lai_watermark_enabled'] ) ? '1' : '0';
        $trust_proxies     = isset( $_POST['nguu_lai_trust_proxies'] ) ? '1' : '0';
        $custom_css        = wp_strip_all_tags( wp_unslash( $_POST['nguu_lai_custom_css'] ?? '' ) );

        update_option( 'nguu_lai_google_client_id', $google_client_id );
        update_option( 'nguu_lai_require_login', $require_login );
        update_option( 'nguu_lai_guest_quota', $guest_quota );
        update_option( 'nguu_lai_watermark_text', $watermark_text );
        update_option( 'nguu_lai_watermark_enabled', $watermark_enabled );
        update_option( 'nguu_lai_trust_proxies', $trust_proxies );
        update_option( 'nguu_lai_custom_css', $custom_css );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'settings',
            'status' => 'settings_saved',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Lưu Danh sách câu thoại từ Textarea hoặc Input List.
     */
    public function handle_save_phrases(): void {
        check_admin_referer( 'nguu_lai_save_phrases_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $clean_phrases = [];

        // Xử lý từ textarea nếu có
        if ( isset( $_POST['nguu_lai_phrases_text'] ) ) {
            $raw_lines = explode( "\n", wp_unslash( $_POST['nguu_lai_phrases_text'] ) );
            foreach ( $raw_lines as $line ) {
                $cleaned = trim( sanitize_text_field( $line ) );
                if ( ! empty( $cleaned ) ) {
                    $clean_phrases[] = $cleaned;
                }
            }
        } elseif ( isset( $_POST['nguu_lai_phrases'] ) && is_array( $_POST['nguu_lai_phrases'] ) ) {
            foreach ( $_POST['nguu_lai_phrases'] as $phrase ) {
                $cleaned = trim( sanitize_text_field( wp_unslash( $phrase ) ) );
                if ( ! empty( $cleaned ) ) {
                    $clean_phrases[] = $cleaned;
                }
            }
        }

        if ( empty( $clean_phrases ) ) {
            $clean_phrases = self::get_default_viral_phrases();
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
     * Khôi phục toàn bộ danh sách câu thoại viral mặc định.
     */
    public function handle_reset_phrases(): void {
        check_admin_referer( 'nguu_lai_reset_phrases_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        update_option( 'nguu_lai_default_phrases', self::get_default_viral_phrases() );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'phrases',
            'status' => 'phrases_reset',
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
                'expires_at' => 0,
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
     * Mở khóa IP.
     */
    public function handle_unblock_ip(): void {
        check_admin_referer( 'nguu_lai_unblock_ip_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        $target_ip = sanitize_text_field( wp_unslash( $_GET['ip'] ?? '' ) );
        $blocked   = get_option( 'nguu_lai_blocked_ips', [] );

        $updated = array_filter( $blocked, function ( $item ) use ( $target_ip ) {
            return ( $item['ip'] ?? '' ) !== $target_ip;
        } );

        update_option( 'nguu_lai_blocked_ips', array_values( $updated ) );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'security',
            'status' => 'ip_unblocked',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Xóa toàn bộ logs.
     */
    public function handle_clear_all_logs(): void {
        check_admin_referer( 'nguu_lai_clear_all_logs_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        Database::clear_logs();

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'logs',
            'status' => 'logs_cleared',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Mở khóa tất cả IP.
     */
    public function handle_clear_all_blocks(): void {
        check_admin_referer( 'nguu_lai_clear_all_blocks_action', 'nguu_lai_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền thực hiện hành động này.', 'nguu-lai' ) );
        }

        update_option( 'nguu_lai_blocked_ips', [] );

        wp_safe_redirect( add_query_arg( [
            'page'   => 'nguu-lai-settings',
            'tab'    => 'security',
            'status' => 'all_blocks_cleared',
        ], admin_url( 'admin.php' ) ) );
        exit;
    }

    /**
     * Render trang giao diện Admin Settings.
     */
    public function render_settings_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Bạn không có quyền truy cập trang này.', 'nguu-lai' ) );
        }

        $current_tab = sanitize_key( $_GET['tab'] ?? 'overview' );
        $status      = sanitize_key( $_GET['status'] ?? '' );
        $today_str   = current_time( 'Y-m-d' );
        $search      = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );

        $preset = sanitize_key( $_GET['preset'] ?? '' );
        if ( 'today' === $preset ) {
            $start_date = $today_str;
            $end_date   = $today_str;
        } elseif ( 'yesterday' === $preset ) {
            $yesterday  = gmdate( 'Y-m-d', strtotime( '-1 day', current_time( 'timestamp' ) ) );
            $start_date = $yesterday;
            $end_date   = $yesterday;
        } elseif ( '7' === $preset ) {
            $start_date = gmdate( 'Y-m-d', strtotime( '-7 days', current_time( 'timestamp' ) ) );
            $end_date   = $today_str;
        } elseif ( '30' === $preset ) {
            $start_date = gmdate( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) );
            $end_date   = $today_str;
        } else {
            $start_date = sanitize_text_field( wp_unslash( $_GET['start_date'] ?? gmdate( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ) );
            $end_date   = sanitize_text_field( wp_unslash( $_GET['end_date'] ?? $today_str ) );
        }

        if ( empty( $start_date ) ) {
            $start_date = gmdate( 'Y-m-d', strtotime( '-30 days', current_time( 'timestamp' ) ) );
        }
        if ( empty( $end_date ) ) {
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
        $custom_css        = get_option( 'nguu_lai_custom_css', '' );
        
        $phrases = get_option( 'nguu_lai_default_phrases', [] );
        if ( empty( $phrases ) || ! is_array( $phrases ) ) {
            $phrases = self::get_default_viral_phrases();
        }

        // Render view template
        include NGUU_LAI_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }
}
