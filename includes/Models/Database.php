<?php
namespace NguuLai\Models;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Model quản lý dữ liệu Database, IP detection, Quotas, Logs & Analytics.
 */
class Database {

    public static function get_table_name(): string {
        global $wpdb;
        return $wpdb->prefix . 'nguu_lai_logs';
    }

    /**
     * Tự động khởi tạo hoặc nâng cấp cấu trúc bảng Nhật ký khi cần.
     */
    public static function create_or_migrate_tables(): void {
        global $wpdb;

        $table_name      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            session_id varchar(64) DEFAULT '' NOT NULL,
            user_id bigint(20) unsigned DEFAULT 0 NOT NULL,
            ip_address varchar(45) DEFAULT '' NOT NULL,
            template varchar(100) DEFAULT '' NOT NULL,
            meme_text text NOT NULL,
            status varchar(20) DEFAULT 'completed' NOT NULL,
            debug_context longtext NULL,
            plugin_version varchar(20) DEFAULT '' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY ip_address (ip_address),
            KEY template (template),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Tự động kiểm tra & sửa các cột cũ nếu bảng được tạo từ phiên bản trước
        $existing_columns = $wpdb->get_col( "DESC {$table_name}", 0 );
        if ( ! empty( $existing_columns ) && is_array( $existing_columns ) ) {
            if ( in_array( 'template_name', $existing_columns, true ) && ! in_array( 'template', $existing_columns, true ) ) {
                $wpdb->query( "ALTER TABLE {$table_name} CHANGE template_name template varchar(100) DEFAULT '' NOT NULL" );
            }
            if ( ! in_array( 'debug_context', $existing_columns, true ) ) {
                $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN debug_context longtext NULL AFTER status" );
            }
            if ( ! in_array( 'plugin_version', $existing_columns, true ) ) {
                $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN plugin_version varchar(20) DEFAULT '' NOT NULL AFTER debug_context" );
            }
            if ( ! in_array( 'template', $existing_columns, true ) ) {
                $wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN template varchar(100) DEFAULT '' NOT NULL AFTER ip_address" );
            }
        }
    }

    /**
     * Nhận diện IP người dùng an toàn (hỗ trợ Cloudflare & Reverse Proxy).
     */
    public static function get_client_ip(): string {
        $trust_proxies = (bool) get_option( 'nguu_lai_trust_proxies', true );

        if ( $trust_proxies ) {
            if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }

            if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                $forwarded = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
                $ip        = trim( $forwarded[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        $remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1';
        return filter_var( $remote_addr, FILTER_VALIDATE_IP ) ? $remote_addr : '127.0.0.1';
    }

    /**
     * Kiểm tra IP có bị chặn trong Blocklist không.
     */
    public static function is_ip_blocked( string $ip ): bool {
        $blocked = get_option( 'nguu_lai_blocked_ips', [] );
        if ( empty( $blocked ) || ! is_array( $blocked ) ) {
            return false;
        }

        $now = time();
        foreach ( $blocked as $item ) {
            if ( isset( $item['ip'] ) && $item['ip'] === $ip ) {
                $expires = intval( $item['expires_at'] ?? 0 );
                if ( 0 === $expires || $expires > $now ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Lấy hạn mức tải còn lại trong ngày (Quota).
     */
    public static function get_quota_status( int $user_id = 0 ): array {
        $is_logged_in = $user_id > 0 || is_user_logged_in();
        $require_login = (bool) get_option( 'nguu_lai_require_login', false );

        if ( $is_logged_in ) {
            return [
                'is_logged_in'    => true,
                'remaining_quota' => -1, // -1 nghĩa là Không giới hạn (Unlimited)
                'daily_limit'     => -1,
                'require_login'   => $require_login,
                'user_id'         => $user_id ?: get_current_user_id(),
            ];
        }

        $ip          = self::get_client_ip();
        $daily_limit = max( 0, intval( get_option( 'nguu_lai_guest_quota', 5 ) ) );

        if ( $require_login ) {
            return [
                'is_logged_in'    => false,
                'remaining_quota' => 0,
                'daily_limit'     => $daily_limit,
                'require_login'   => true,
                'user_id'         => 0,
            ];
        }

        $transient_key = 'nguu_lai_quota_' . md5( $ip . '_' . date( 'Y-m-d' ) );
        $used_today    = intval( get_transient( $transient_key ) );
        $remaining     = max( 0, $daily_limit - $used_today );

        return [
            'is_logged_in'    => false,
            'remaining_quota' => $remaining,
            'daily_limit'     => $daily_limit,
            'used_today'      => $used_today,
            'require_login'   => false,
            'user_id'         => 0,
        ];
    }

    /**
     * Tăng số lượt đã sử dụng của IP khách.
     */
    public static function consume_guest_quota(): int {
        $ip            = self::get_client_ip();
        $transient_key = 'nguu_lai_quota_' . md5( $ip . '_' . date( 'Y-m-d' ) );
        $used          = intval( get_transient( $transient_key ) );
        $used++;

        // Tính thời gian hết hạn đến hết ngày (nửa đêm)
        $midnight = strtotime( 'tomorrow midnight' );
        $ttl      = max( 60, $midnight - time() );
        set_transient( $transient_key, $used, $ttl );

        $daily_limit = max( 0, intval( get_option( 'nguu_lai_guest_quota', 5 ) ) );
        return max( 0, $daily_limit - $used );
    }

    /**
     * Ghi nhận log khi người dùng tạo/tải meme.
     */
    public static function insert_log( array $data ): int {
        global $wpdb;

        $table = self::get_table_name();
        $ip    = self::get_client_ip();

        $session_id  = sanitize_text_field( $data['session_id'] ?? wp_generate_uuid4() );
        $user_id     = max( 0, intval( $data['user_id'] ?? get_current_user_id() ) );
        $template    = sanitize_text_field( $data['template'] ?? 'niulai_01.webp' );
        $meme_text   = sanitize_text_field( $data['meme_text'] ?? '' );
        $status      = sanitize_key( $data['status'] ?? 'completed' );
        $context_arr = is_array( $data['context'] ?? null ) ? $data['context'] : [];
        $context_json = wp_json_encode( $context_arr );

        $result = $wpdb->insert(
            $table,
            [
                'session_id'     => $session_id,
                'user_id'        => $user_id,
                'ip_address'     => $ip,
                'template'       => $template,
                'meme_text'      => $meme_text,
                'status'         => $status,
                'debug_context'  => $context_json,
                'plugin_version' => NGUU_LAI_VERSION,
                'created_at'     => current_time( 'mysql' ),
            ],
            [ '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        // Nếu bảng chưa có hoặc lỗi cấu trúc, tự động sửa và thử lại
        if ( false === $result ) {
            self::create_or_migrate_tables();
            $result = $wpdb->insert(
                $table,
                [
                    'session_id'     => $session_id,
                    'user_id'        => $user_id,
                    'ip_address'     => $ip,
                    'template'       => $template,
                    'meme_text'      => $meme_text,
                    'status'         => $status,
                    'debug_context'  => $context_json,
                    'plugin_version' => NGUU_LAI_VERSION,
                    'created_at'     => current_time( 'mysql' ),
                ],
                [ '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
            );
        }

        return $result ? (int) $wpdb->insert_id : 0;
    }

    /**
     * Lấy danh sách logs kèm phân trang và lọc theo ngày.
     */
    public static function get_logs( int $limit = 50, int $offset = 0, string $start_date = '', string $end_date = '', string $search = '' ): array {
        global $wpdb;

        self::create_or_migrate_tables();

        $table  = self::get_table_name();
        $where  = [ '1=1' ];
        $params = [];

        if ( ! empty( $start_date ) ) {
            $where[]  = 'created_at >= %s';
            $params[] = $start_date . ' 00:00:00';
        }

        if ( ! empty( $end_date ) ) {
            $where[]  = 'created_at <= %s';
            $params[] = $end_date . ' 23:59:59';
        }

        if ( ! empty( $search ) ) {
            $where[]  = '(meme_text LIKE %s OR ip_address LIKE %s OR template LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $where_clause = implode( ' AND ', $where );
        $query        = "SELECT * FROM {$table} WHERE {$where_clause} ORDER BY id DESC LIMIT %d OFFSET %d";
        $params[]     = $limit;
        $params[]     = $offset;

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $wpdb->get_results( $wpdb->prepare( $query, $params ) ) ?: [];
    }

    /**
     * Đếm tổng số bản ghi logs theo bộ lọc.
     */
    public static function count_logs( string $start_date = '', string $end_date = '', string $search = '' ): int {
        global $wpdb;

        self::create_or_migrate_tables();

        $table  = self::get_table_name();
        $where  = [ '1=1' ];
        $params = [];

        if ( ! empty( $start_date ) ) {
            $where[]  = 'created_at >= %s';
            $params[] = $start_date . ' 00:00:00';
        }

        if ( ! empty( $end_date ) ) {
            $where[]  = 'created_at <= %s';
            $params[] = $end_date . ' 23:59:59';
        }

        if ( ! empty( $search ) ) {
            $where[]  = '(meme_text LIKE %s OR ip_address LIKE %s OR template LIKE %s)';
            $like     = '%' . $wpdb->esc_like( $search ) . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        $where_clause = implode( ' AND ', $where );
        $query        = "SELECT COUNT(id) FROM {$table} WHERE {$where_clause}";

        if ( ! empty( $params ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            return (int) $wpdb->get_var( $wpdb->prepare( $query, $params ) );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return (int) $wpdb->get_var( $query );
    }

    /**
     * Thống kê tổng hợp (Analytics): Top templates, tổng lượt tải, số người dùng.
     */
    public static function get_analytics( string $start_date = '', string $end_date = '' ): array {
        global $wpdb;

        self::create_or_migrate_tables();

        $table  = self::get_table_name();
        $where  = [ '1=1' ];
        $params = [];

        if ( ! empty( $start_date ) ) {
            $where[]  = 'created_at >= %s';
            $params[] = $start_date . ' 00:00:00';
        }

        if ( ! empty( $end_date ) ) {
            $where[]  = 'created_at <= %s';
            $params[] = $end_date . ' 23:59:59';
        }

        $where_clause = implode( ' AND ', $where );

        // Top Templates
        $top_query = "SELECT template, COUNT(id) as total_count 
                      FROM {$table} 
                      WHERE {$where_clause} 
                      GROUP BY template 
                      ORDER BY total_count DESC 
                      LIMIT 10";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $top_templates = ! empty( $params ) ? $wpdb->get_results( $wpdb->prepare( $top_query, $params ) ) : $wpdb->get_results( $top_query );

        // Unique IPs / Users count
        $unique_query = "SELECT COUNT(DISTINCT ip_address) as unique_ips, COUNT(DISTINCT user_id) as unique_users, COUNT(id) as total_memes
                         FROM {$table} 
                         WHERE {$where_clause}";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $summary = ! empty( $params ) ? $wpdb->get_row( $wpdb->prepare( $unique_query, $params ) ) : $wpdb->get_row( $unique_query );

        return [
            'total_memes'   => (int) ( $summary->total_memes ?? 0 ),
            'unique_ips'    => (int) ( $summary->unique_ips ?? 0 ),
            'unique_users'  => (int) ( $summary->unique_users ?? 0 ),
            'top_templates' => $top_templates ?: [],
        ];
    }

    /**
     * Xóa toàn bộ logs.
     */
    public static function clear_logs(): bool {
        global $wpdb;
        $table = self::get_table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
        return (bool) $wpdb->query( "TRUNCATE TABLE {$table}" );
    }
}
