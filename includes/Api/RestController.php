<?php
namespace NguuLai\Api;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use NguuLai\Models\Database;

/**
 * Controller xử lý toàn bộ REST API của plugin Ngưu Lai.
 */
class RestController extends WP_REST_Controller {

    const REST_NAMESPACE = 'nguu-lai/v1';

    public function register_routes(): void {
        // 1. Endpoint Đăng nhập Google
        register_rest_route(
            self::REST_NAMESPACE,
            '/google-login',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_google_login' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'credential' => [
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

        // 2. Endpoint Lấy hạn mức Quota hiện tại
        register_rest_route(
            self::REST_NAMESPACE,
            '/quota',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'handle_get_quota' ],
                'permission_callback' => '__return_true',
            ]
        );

        // 3. Endpoint Ghi nhận Tải Meme & Cập nhật Nhật ký
        register_rest_route(
            self::REST_NAMESPACE,
            '/update-log',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'handle_update_log' ],
                'permission_callback' => '__return_true',
                'args'                => [
                    'template' => [
                        'required'          => false,
                        'type'              => 'string',
                        'default'           => 'niulai_01.webp',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'text' => [
                        'required'          => false,
                        'type'              => 'string',
                        'default'           => '',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'session_id' => [
                        'required'          => false,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ]
        );

        // 4. Endpoint Xuất Logs cho Admin
        register_rest_route(
            self::REST_NAMESPACE,
            '/export-logs',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'handle_export_logs' ],
                'permission_callback' => function () {
                    return current_user_can( 'manage_options' );
                },
            ]
        );
    }

    /**
     * Xử lý xác thực Google ID Token & tạo phiên làm việc WP.
     */
    public function handle_google_login( WP_REST_Request $request ): WP_REST_Response {
        $nonce = $request->get_header( 'X-WP-Nonce' );
        if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Kiểm tra bảo mật thất bại (Mã Nonce không hợp lệ hoặc đã hết hạn). Vui lòng tải lại trang.',
            ], 403 );
        }

        $client_id = get_option( 'nguu_lai_google_client_id', '' );
        if ( empty( $client_id ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Google Client ID chưa được thiết lập trong trang quản trị.',
            ], 400 );
        }

        $credential = $request->get_param( 'credential' );
        $verify_url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode( $credential );

        $response = wp_remote_get( $verify_url, [ 'timeout' => 15 ] );
        if ( is_wp_error( $response ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ Google để xác thực mã token.',
            ], 500 );
        }

        $status_code = wp_remote_retrieve_response_code( $response );
        $body        = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body, true );

        if ( 200 !== $status_code || empty( $data ) || isset( $data['error'] ) ) {
            $err_desc = $data['error_description'] ?? ( $data['error'] ?? 'Token Google không hợp lệ.' );
            return new WP_REST_Response( [
                'success' => false,
                'message' => $err_desc,
            ], 400 );
        }

        // 1. Kiểm tra Audience
        if ( ( $data['aud'] ?? '' ) !== $client_id ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Xác thực Audience thất bại (Client ID không khớp).',
            ], 400 );
        }

        // 2. Kiểm tra Issuer
        $allowed_issuers = [ 'https://accounts.google.com', 'accounts.google.com' ];
        if ( ! in_array( $data['iss'] ?? '', $allowed_issuers, true ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Xác thực nhà phát hành Issuer thất bại.',
            ], 400 );
        }

        // 3. Kiểm tra Email Verified
        if ( empty( $data['email_verified'] ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Địa chỉ Email Google của bạn chưa được xác minh.',
            ], 400 );
        }

        // 4. Kiểm tra Expiry
        if ( intval( $data['exp'] ?? 0 ) <= time() ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Phiên làm việc của Token Google đã hết hạn.',
            ], 400 );
        }

        $email   = strtolower( trim( $data['email'] ?? '' ) );
        $name    = trim( $data['name'] ?? '' );
        $sub     = trim( $data['sub'] ?? '' );
        $picture = trim( $data['picture'] ?? '' );

        if ( empty( $email ) || empty( $sub ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Dữ liệu hồ sơ Google không đầy đủ.',
            ], 400 );
        }

        // Tìm User theo Google Sub Meta
        $user        = null;
        $users_query = get_users( [
            'meta_key'   => 'nguu_lai_google_sub',
            'meta_value' => $sub,
            'number'     => 1,
        ] );

        if ( ! empty( $users_query ) ) {
            $user = $users_query[0];
        } else {
            // Tìm theo email nếu chưa có meta sub
            $user = get_user_by( 'email', $email );
            if ( $user ) {
                update_user_meta( $user->ID, 'nguu_lai_google_sub', $sub );
            }
        }

        // Nếu người dùng chưa tồn tại -> Tạo mới tài khoản WordPress an toàn
        if ( ! $user ) {
            $username = 'google_' . substr( md5( $sub ), 0, 10 );
            if ( username_exists( $username ) ) {
                $username .= '_' . wp_rand( 100, 999 );
            }

            $random_password = wp_generate_password( 24, true );
            $user_id         = wp_create_user( $username, $random_password, $email );

            if ( is_wp_error( $user_id ) ) {
                return new WP_REST_Response( [
                    'success' => false,
                    'message' => 'Không thể khởi tạo tài khoản người dùng mới: ' . $user_id->get_error_message(),
                ], 500 );
            }

            $user = get_user_by( 'id', $user_id );
            wp_update_user( [
                'ID'           => $user->ID,
                'display_name' => $name ?: $username,
                'first_name'   => $name,
            ] );

            update_user_meta( $user->ID, 'nguu_lai_google_sub', $sub );
            if ( ! empty( $picture ) ) {
                update_user_meta( $user->ID, 'nguu_lai_google_avatar', esc_url_raw( $picture ) );
            }
        }

        // Khởi tạo phiên làm việc WordPress (Set Auth Cookie)
        wp_clear_auth_cookie();
        wp_set_current_user( $user->ID );
        wp_set_auth_cookie( $user->ID, true );

        $avatar_url = get_user_meta( $user->ID, 'nguu_lai_google_avatar', true );
        if ( empty( $avatar_url ) ) {
            $avatar_url = get_avatar_url( $user->ID, [ 'size' => 64 ] );
        }

        return new WP_REST_Response( [
            'success' => true,
            'message' => 'Đăng nhập Google thành công! Bạn có thể tạo và tải meme không giới hạn.',
            'data'    => [
                'user_id'         => $user->ID,
                'name'            => $user->display_name,
                'email'           => $user->user_email,
                'avatar'          => $avatar_url,
                'is_logged_in'    => true,
                'remaining_quota' => -1, // Không giới hạn
            ],
        ], 200 );
    }

    /**
     * Lấy trạng thái Hạn mức Quota.
     */
    public function handle_get_quota(): WP_REST_Response {
        $quota = Database::get_quota_status();
        return new WP_REST_Response( [
            'success' => true,
            'data'    => $quota,
        ], 200 );
    }

    /**
     * Ghi nhận lượt tải meme & cập nhật quota.
     */
    public function handle_update_log( WP_REST_Request $request ): WP_REST_Response {
        $ip = Database::get_client_ip();
        if ( Database::is_ip_blocked( $ip ) ) {
            return new WP_REST_Response( [
                'success' => false,
                'message' => 'Địa chỉ IP của bạn đang bị tạm khóa do vi phạm chính sách sử dụng.',
            ], 403 );
        }

        $user_id      = get_current_user_id();
        $quota_status = Database::get_quota_status( $user_id );

        // Kiểm tra xem có được phép tải không
        if ( ! $quota_status['is_logged_in'] && $quota_status['remaining_quota'] <= 0 ) {
            return new WP_REST_Response( [
                'success'       => false,
                'require_login' => true,
                'message'       => 'Bạn đã dùng hết số lượt tải miễn phí trong ngày. Vui lòng đăng nhập Google để tiếp tục tải không giới hạn!',
            ], 403 );
        }

        $template   = sanitize_text_field( $request->get_param( 'template' ) ?: 'niulai_01.webp' );
        $text       = sanitize_text_field( $request->get_param( 'text' ) ?: '' );
        $session_id = sanitize_text_field( $request->get_param( 'session_id' ) ?: wp_generate_uuid4() );

        // Trừ quota khách nếu là khách
        $remaining = -1;
        if ( ! $quota_status['is_logged_in'] ) {
            $remaining = Database::consume_guest_quota();
        }

        // Lưu bản ghi nhật ký
        $log_id = Database::insert_log( [
            'session_id' => $session_id,
            'user_id'    => $user_id,
            'template'   => $template,
            'meme_text'  => $text,
            'status'     => 'completed',
            'context'    => [
                'user_agent' => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
            ],
        ] );

        // Kích hoạt action hook để mở rộng
        do_action( 'nguu_lai_meme_downloaded', $log_id, [
            'user_id'    => $user_id,
            'template'   => $template,
            'text'       => $text,
            'ip_address' => $ip,
        ] );

        return new WP_REST_Response( [
            'success' => true,
            'data'    => [
                'log_id'          => $log_id,
                'remaining_quota' => $remaining,
                'is_logged_in'    => $quota_status['is_logged_in'],
            ],
        ], 200 );
    }

    /**
     * Xuất logs cho Quản trị viên.
     */
    public function handle_export_logs( WP_REST_Request $request ): WP_REST_Response {
        $start_date = sanitize_text_field( $request->get_param( 'start_date' ) ?: '' );
        $end_date   = sanitize_text_field( $request->get_param( 'end_date' ) ?: '' );

        $logs = Database::get_logs( 5000, 0, $start_date, $end_date );

        return new WP_REST_Response( [
            'success' => true,
            'data'    => $logs,
        ], 200 );
    }
}
