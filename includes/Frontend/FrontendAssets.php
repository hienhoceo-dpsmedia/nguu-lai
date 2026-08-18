<?php
namespace NguuLai\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NguuLai\Models\Database;

/**
 * Quản lý Enqueue Assets (CSS, JS, Google Fonts, Google GIS) cho Frontend.
 */
class FrontendAssets {

    public function register_assets(): void {
        $css_file = NGUU_LAI_PLUGIN_DIR . 'assets/css/meme-workbench.css';
        $js_auth  = NGUU_LAI_PLUGIN_DIR . 'assets/js/google-auth.js';
        $js_work  = NGUU_LAI_PLUGIN_DIR . 'assets/js/meme-workbench.js';

        $ver_css  = file_exists( $css_file ) ? filemtime( $css_file ) : NGUU_LAI_VERSION;
        $ver_auth = file_exists( $js_auth ) ? filemtime( $js_auth ) : NGUU_LAI_VERSION;
        $ver_work = file_exists( $js_work ) ? filemtime( $js_work ) : NGUU_LAI_VERSION;

        // Google Fonts chuẩn Tiếng Việt (Subset Vietnamese 100% cho cả Canvas và toàn bộ UI)
        $fonts_url = 'https://fonts.googleapis.com/css2?family=Anton&family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=Bungee&family=Caveat:wght@600;700&family=Comfortaa:wght@600;700&family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,700&family=Oswald:wght@500;600;700&family=Patrick+Hand&family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&family=Roboto:ital,wght@0,400;0,500;0,700;0,900;1,400;1,700&display=swap&subset=vietnamese';
        wp_register_style(
            'nguu-lai-google-fonts',
            $fonts_url,
            [],
            null
        );

        // CSS Giao diện Meme Workbench
        wp_register_style(
            'nguu-lai-workbench',
            NGUU_LAI_PLUGIN_URL . 'assets/css/meme-workbench.css',
            [ 'nguu-lai-google-fonts' ],
            $ver_css
        );

        // Google Identity Services SDK
        wp_register_script(
            'google-gsi-client',
            'https://accounts.google.com/gsi/client',
            [],
            null,
            true
        );

        // JS Google Auth Helper
        wp_register_script(
            'nguu-lai-auth',
            NGUU_LAI_PLUGIN_URL . 'assets/js/google-auth.js',
            [ 'google-gsi-client' ],
            $ver_auth,
            true
        );

        // JS Meme Workbench Core Engine
        wp_register_script(
            'nguu-lai-workbench',
            NGUU_LAI_PLUGIN_URL . 'assets/js/meme-workbench.js',
            [ 'nguu-lai-auth' ],
            $ver_work,
            true
        );

        // Chống LiteSpeed Cache / Rocket Loader tối ưu làm hỏng GSI và Auth scripts
        add_filter( 'script_loader_tag', function ( $tag, $handle ) {
            if ( in_array( $handle, [ 'google-gsi-client', 'nguu-lai-auth', 'nguu-lai-workbench' ], true ) ) {
                if ( false === strpos( $tag, 'data-no-optimize' ) ) {
                    $tag = str_replace( '<script ', '<script data-no-optimize="1" data-cfasync="false" ', $tag );
                }
            }
            return $tag;
        }, 10, 2 );
    }

    /**
     * Enqueue và truyền dữ liệu JSON an toàn khi shortcode được render.
     */
    public static function enqueue_workbench( array $custom_config = [] ): void {
        wp_enqueue_style( 'nguu-lai-google-fonts' );
        wp_enqueue_style( 'nguu-lai-workbench' );
        wp_enqueue_script( 'google-gsi-client' );
        wp_enqueue_script( 'nguu-lai-auth' );
        wp_enqueue_script( 'nguu-lai-workbench' );

        $user_id      = get_current_user_id();
        $user         = wp_get_current_user();
        $quota_status = Database::get_quota_status( $user_id );

        $avatar_url = '';
        if ( $user_id > 0 ) {
            $avatar_url = get_user_meta( $user_id, 'nguu_lai_google_avatar', true );
            if ( empty( $avatar_url ) ) {
                $avatar_url = get_avatar_url( $user_id, [ 'size' => 64 ] );
            }
        }

        $default_phrases = get_option( 'nguu_lai_default_phrases', [] );
        $phrases         = apply_filters( 'nguu_lai_meme_phrases', $default_phrases );

        // 16 phôi ảnh WebP mặc định
        $templates = [];
        for ( $i = 1; $i <= 16; $i++ ) {
            $num         = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
            $templates[] = NGUU_LAI_PLUGIN_URL . "assets/memes/niulai_{$num}.webp";
        }
        $templates = apply_filters( 'nguu_lai_meme_templates', $templates );

        // Danh sách 10 Fonts Google chuẩn Tiếng Việt 100%
        $vietnamese_fonts = [
            [ 'name' => 'Montserrat', 'label' => 'Montserrat (Mặc định)', 'weight' => '900', 'family' => 'Montserrat, sans-serif' ],
            [ 'name' => 'Be Vietnam Pro', 'label' => 'Be Vietnam Pro (Chuẩn quốc tế)', 'weight' => '900', 'family' => '"Be Vietnam Pro", sans-serif' ],
            [ 'name' => 'Anton', 'label' => 'Anton (In hoa đậm)', 'weight' => '400', 'family' => 'Anton, sans-serif' ],
            [ 'name' => 'Oswald', 'label' => 'Oswald (Poster mạnh mẽ)', 'weight' => '700', 'family' => 'Oswald, sans-serif' ],
            [ 'name' => 'Bungee', 'label' => 'Bungee (Khối Pop-art)', 'weight' => '400', 'family' => 'Bungee, cursive' ],
            [ 'name' => 'Comfortaa', 'label' => 'Comfortaa (Bo tròn cute)', 'weight' => '700', 'family' => 'Comfortaa, cursive' ],
            [ 'name' => 'Caveat', 'label' => 'Caveat (Viết tay tự nhiên)', 'weight' => '700', 'family' => 'Caveat, cursive' ],
            [ 'name' => 'Patrick Hand', 'label' => 'Patrick Hand (Truyện tranh)', 'weight' => '400', 'family' => '"Patrick Hand", cursive' ],
            [ 'name' => 'Playfair Display', 'label' => 'Playfair Display (Sang trọng)', 'weight' => '900', 'family' => '"Playfair Display", serif' ],
            [ 'name' => 'Roboto', 'label' => 'Roboto (Tiêu chuẩn)', 'weight' => '900', 'family' => 'Roboto, sans-serif' ],
        ];
        $vietnamese_fonts = apply_filters( 'nguu_lai_vietnamese_fonts', $vietnamese_fonts );

        $watermark_text    = get_option( 'nguu_lai_watermark_text', 'niulai.wiki' );
        $watermark_enabled = (bool) get_option( 'nguu_lai_watermark_enabled', '1' );
        $google_client_id  = get_option( 'nguu_lai_google_client_id', '' );

        $data = array_merge( [
            'rest_url'          => esc_url_raw( rest_url( 'nguu-lai/v1' ) ),
            'rest_nonce'        => wp_create_nonce( 'wp_rest' ),
            'google_client_id'  => esc_attr( $google_client_id ),
            'is_logged_in'      => $quota_status['is_logged_in'],
            'user'              => [
                'id'     => $user_id,
                'name'   => $user_id > 0 ? ( $user->display_name ?: $user->user_login ) : '',
                'avatar' => $avatar_url,
            ],
            'quota'             => $quota_status,
            'phrases'           => array_values( $phrases ),
            'templates'         => array_values( $templates ),
            'fonts'             => array_values( $vietnamese_fonts ),
            'watermark_text'    => $watermark_text,
            'watermark_enabled' => $watermark_enabled,
            'i18n'              => [
                'canvas_label'    => 'Trình xem trước meme Ngưu Lai thời gian thực',
                'current'         => 'Đang chọn',
                'use'             => 'Dùng mẫu này',
                'download_ready'  => 'Đã tạo và tải meme thành công! 🎉',
                'quota_exceeded'  => 'Bạn đã dùng hết số lượt tải miễn phí hôm nay. Vui lòng đăng nhập Google để tiếp tục tải không giới hạn!',
                'upload_error'    => 'Không thể tải ảnh. Vui lòng chọn định dạng ảnh hợp lệ (PNG, JPG, WebP).',
                'image_loaded'    => 'Đã chọn ảnh của bạn thành công!',
                'phrase_selected' => 'Đã áp dụng câu thoại mới!',
                'text_empty'      => 'Vui lòng nhập nội dung chữ cho meme!',
                'login_prompt'    => 'Đăng nhập với Google để tải meme không giới hạn!',
                'logging_in'      => 'Đang xác thực tài khoản Google...',
                'login_success'   => 'Đăng nhập Google thành công! Lượt tải của bạn: Không giới hạn ✨',
                'login_failed'    => 'Đăng nhập Google thất bại. Vui lòng thử lại.',
            ],
        ], $custom_config );

        wp_localize_script( 'nguu-lai-workbench', 'nguuLaiData', $data );
        wp_add_inline_script( 'nguu-lai-workbench', 'window.nguuLaiData = ' . wp_json_encode( $data ) . ';', 'before' );

        // Chèn Custom CSS từ Admin Settings vào frontend (nếu có)
        $custom_css = get_option( 'nguu_lai_custom_css', '' );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'nguu-lai-workbench', $custom_css );
        }
    }
}
