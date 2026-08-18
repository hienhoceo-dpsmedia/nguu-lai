<?php
namespace NguuLai\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NguuLai\Models\Database;

/**
 * Xử lý Shortcode [nguu_lai_meme].
 */
class Shortcode {

    public function register_shortcode(): void {
        add_shortcode( 'nguu_lai_meme', [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts = [] ): string {
        $raw_atts = is_array( $atts ) ? $atts : [];

        $attributes = shortcode_atts( [
            'default_text'     => 'Hả?',
            'default_template' => 1,
            'default_font'     => 'Montserrat',
            'watermark'        => null,
            'watermark_text'   => null,
            'require_login'    => null,
            'theme'            => 'dark',
            'title'            => 'Để Ngưu Lại Nói Thay Lời Bạn.',
            'aside'            => 'Chọn một biểu cảm, thêm một dòng chữ, căn chỉnh trực tiếp và xuất ảnh PNG chuẩn 900 × 900.',
        ], $raw_atts, 'nguu_lai_meme' );

        $user_id      = get_current_user_id();
        $user         = wp_get_current_user();
        $quota_status = Database::get_quota_status( $user_id );

        // 1. Xử lý Bắt buộc đăng nhập
        $opt_require_login = (bool) get_option( 'nguu_lai_require_login', false );
        if ( null !== $attributes['require_login'] ) {
            $require_login = '1' === (string) $attributes['require_login'];
        } else {
            $require_login = $opt_require_login;
        }

        if ( $require_login && ! $quota_status['is_logged_in'] ) {
            $quota_status['require_login']   = true;
            $quota_status['remaining_quota'] = 0;
        }

        // 2. Xử lý Watermark Text
        $opt_watermark_text = get_option( 'nguu_lai_watermark_text', 'niulai.wiki' );
        if ( null !== $attributes['watermark_text'] && '' !== trim( $attributes['watermark_text'] ) ) {
            $watermark_text = sanitize_text_field( $attributes['watermark_text'] );
        } else {
            $watermark_text = ! empty( $opt_watermark_text ) ? $opt_watermark_text : 'niulai.wiki';
        }

        // 3. Xử lý Trạng thái Watermark bật/tắt
        $opt_watermark_enabled = (bool) get_option( 'nguu_lai_watermark_enabled', '1' );
        if ( null !== $attributes['watermark'] ) {
            $watermark_enabled = '1' === (string) $attributes['watermark'];
        } else {
            $watermark_enabled = $opt_watermark_enabled;
        }

        $avatar_url = '';
        if ( $user_id > 0 ) {
            $avatar_url = get_user_meta( $user_id, 'nguu_lai_google_avatar', true );
            if ( empty( $avatar_url ) ) {
                $avatar_url = get_avatar_url( $user_id, [ 'size' => 64 ] );
            }
        }

        // Lấy danh sách câu thoại từ Admin
        $default_phrases = get_option( 'nguu_lai_default_phrases', [] );
        if ( empty( $default_phrases ) || ! is_array( $default_phrases ) ) {
            $default_phrases = [
                'Hả?',
                'Nghiêm túc đi bạn ơi.',
                'Nói lại lần nữa xem?',
                'Cạn lời luôn á.',
                'Biết nói gì bây giờ?',
                'Đúng rồi, bạn là nhất!',
                'Ủa alo? Gì vậy trời?',
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
        $phrases = apply_filters( 'nguu_lai_meme_phrases', $default_phrases );

        // 16 phôi ảnh WebP mặc định
        $templates = [];
        for ( $i = 1; $i <= 16; $i++ ) {
            $num         = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
            $templates[] = NGUU_LAI_PLUGIN_URL . "assets/memes/niulai_{$num}.webp";
        }
        $templates = apply_filters( 'nguu_lai_meme_templates', $templates );

        // Danh sách 10 Fonts Google chuẩn Tiếng Việt 100%
        $vietnamese_fonts = [
            [ 'name' => 'Montserrat', 'label' => 'Montserrat (Mặc định - Hiện đại & Đậm nét)', 'weight' => '900', 'family' => 'Montserrat, sans-serif' ],
            [ 'name' => 'Be Vietnam Pro', 'label' => 'Be Vietnam Pro (Chuẩn tiếng Việt quốc tế)', 'weight' => '900', 'family' => '"Be Vietnam Pro", sans-serif' ],
            [ 'name' => 'Anton', 'label' => 'Anton (Meme kinh điển - Cao & Đậm)', 'weight' => '400', 'family' => 'Anton, sans-serif' ],
            [ 'name' => 'Oswald', 'label' => 'Oswald (Mạnh mẽ - Phong cách Poster)', 'weight' => '700', 'family' => 'Oswald, sans-serif' ],
            [ 'name' => 'Bungee', 'label' => 'Bungee (Khối hộp Pop-art phá cách)', 'weight' => '400', 'family' => 'Bungee, cursive' ],
            [ 'name' => 'Comfortaa', 'label' => 'Comfortaa (Bo tròn - Dễ thương, hài hước)', 'weight' => '700', 'family' => 'Comfortaa, cursive' ],
            [ 'name' => 'Caveat', 'label' => 'Caveat (Chữ viết tay tự nhiên, phóng khoáng)', 'weight' => '700', 'family' => 'Caveat, cursive' ],
            [ 'name' => 'Patrick Hand', 'label' => 'Patrick Hand (Viết tay truyện tranh thân thiện)', 'weight' => '400', 'family' => '"Patrick Hand", cursive' ],
            [ 'name' => 'Playfair Display', 'label' => 'Playfair Display (Báo chí - Kịch tính & Sang trọng)', 'weight' => '900', 'family' => '"Playfair Display", serif' ],
            [ 'name' => 'Roboto', 'label' => 'Roboto (Rõ ràng, đơn giản, dễ đọc)', 'weight' => '900', 'family' => 'Roboto, sans-serif' ],
        ];
        $vietnamese_fonts = apply_filters( 'nguu_lai_vietnamese_fonts', $vietnamese_fonts );

        $google_client_id = get_option( 'nguu_lai_google_client_id', '' );
        $initial_template = max( 0, min( count( $templates ) - 1, intval( $attributes['default_template'] ) - 1 ) );
        $initial_font     = sanitize_text_field( $attributes['default_font'] ?: 'Montserrat' );

        $js_data = [
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
            'initial_font'      => $initial_font,
            'watermark_text'    => $watermark_text,
            'watermark_enabled' => $watermark_enabled,
            'initial_text'      => sanitize_text_field( $attributes['default_text'] ),
            'initial_template'  => $initial_template,
            'i18n'              => [
                'canvas_label'    => 'Trình xem trước meme Ngưu Lại thời gian thực',
                'current'         => 'Đang chọn',
                'use'             => 'Dùng mẫu này',
                'download_ready'  => 'Đã tạo và tải meme thành công! 🎉',
                'quota_exceeded'  => $require_login ? 'Trang yêu cầu đăng nhập Google để tải meme.' : 'Bạn đã dùng hết số lượt tải miễn phí hôm nay. Vui lòng đăng nhập Google để tiếp tục tải không giới hạn!',
                'upload_error'    => 'Không thể tải ảnh. Vui lòng chọn định dạng ảnh hợp lệ (PNG, JPG, WebP).',
                'image_loaded'    => 'Đã chọn ảnh của bạn thành công!',
                'phrase_selected' => 'Đã áp dụng câu thoại mới!',
                'text_empty'      => 'Vui lòng nhập nội dung chữ cho meme!',
                'login_prompt'    => 'Đăng nhập với Google để tải meme không giới hạn!',
                'logging_in'      => 'Đang xác thực tài khoản Google...',
                'login_success'   => 'Đăng nhập Google thành công! Lượt tải của bạn: Không giới hạn ✨',
                'login_failed'    => 'Đăng nhập Google thất bại. Vui lòng thử lại.',
            ],
        ];

        // Enqueue Assets
        FrontendAssets::enqueue_workbench( $js_data );

        // Render template view
        ob_start();
        $template_file = NGUU_LAI_PLUGIN_DIR . 'templates/frontend/meme-workbench.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        return (string) ob_get_clean();
    }
}
