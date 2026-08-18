<?php
namespace NguuLai\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NguuLai\Models\Database;
use NguuLai\Core\I18n;

/**
 * Xử lý Shortcode [nguu_lai_meme] hỗ trợ đa ngôn ngữ tự động (vi, en, zh).
 */
class Shortcode {

    public function register_shortcode(): void {
        add_shortcode( 'nguu_lai_meme', [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts = [] ): string {
        $raw_atts = is_array( $atts ) ? $atts : [];

        // 1. Nhận diện ngôn ngữ hiện tại
        $lang         = I18n::get_current_language();
        $translations = I18n::get_translations( $lang );

        $attributes = shortcode_atts( [
            'default_text'     => $translations['default_text'] ?? 'Hả?',
            'default_template' => 1,
            'default_font'     => 'Montserrat',
            'watermark'        => null,
            'watermark_text'   => null,
            'require_login'    => null,
            'theme'            => 'dark',
            'title'            => $translations['section_title'] ?? 'Để Ngưu Lai Nói Thay Lời Bạn',
            'aside'            => $translations['section_aside'] ?? 'Tạo meme PNG 900×900 cực nét.',
        ], $raw_atts, 'nguu_lai_meme' );

        $user_id      = get_current_user_id();
        $user         = wp_get_current_user();
        $quota_status = Database::get_quota_status( $user_id );

        // 2. Xử lý Bắt buộc đăng nhập
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

        // 3. Xử lý Watermark Text
        $opt_watermark_text = get_option( 'nguu_lai_watermark_text', 'niulai.wiki' );
        if ( null !== $attributes['watermark_text'] && '' !== trim( $attributes['watermark_text'] ) ) {
            $watermark_text = sanitize_text_field( $attributes['watermark_text'] );
        } else {
            $watermark_text = ! empty( $opt_watermark_text ) ? $opt_watermark_text : 'niulai.wiki';
        }

        // 4. Xử lý Trạng thái Watermark bật/tắt
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

        // 5. Lấy danh sách câu thoại theo ngôn ngữ
        $phrases = I18n::get_phrases_for_lang( $lang );
        $phrases = apply_filters( 'nguu_lai_meme_phrases', $phrases, $lang );

        // 6. 16 phôi ảnh WebP mặc định
        $templates = [];
        for ( $i = 1; $i <= 16; $i++ ) {
            $num         = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
            $templates[] = NGUU_LAI_PLUGIN_URL . "assets/memes/niulai_{$num}.webp";
        }
        $templates = apply_filters( 'nguu_lai_meme_templates', $templates );

        // 7. Danh sách Fonts Google
        $vietnamese_fonts = [
            [ 'name' => 'Montserrat', 'label' => 'Montserrat', 'weight' => '900', 'family' => 'Montserrat, sans-serif' ],
            [ 'name' => 'Be Vietnam Pro', 'label' => 'Be Vietnam Pro', 'weight' => '900', 'family' => '"Be Vietnam Pro", sans-serif' ],
            [ 'name' => 'Anton', 'label' => 'Anton (Bold)', 'weight' => '400', 'family' => 'Anton, sans-serif' ],
            [ 'name' => 'Oswald', 'label' => 'Oswald (Poster)', 'weight' => '700', 'family' => 'Oswald, sans-serif' ],
            [ 'name' => 'Bungee', 'label' => 'Bungee (Pop-art)', 'weight' => '400', 'family' => 'Bungee, cursive' ],
            [ 'name' => 'Comfortaa', 'label' => 'Comfortaa (Rounded)', 'weight' => '700', 'family' => 'Comfortaa, cursive' ],
            [ 'name' => 'Caveat', 'label' => 'Caveat (Handwriting)', 'weight' => '700', 'family' => 'Caveat, cursive' ],
            [ 'name' => 'Patrick Hand', 'label' => 'Patrick Hand (Comic)', 'weight' => '400', 'family' => '"Patrick Hand", cursive' ],
            [ 'name' => 'Playfair Display', 'label' => 'Playfair Display (Serif)', 'weight' => '900', 'family' => '"Playfair Display", serif' ],
            [ 'name' => 'Roboto', 'label' => 'Roboto (Standard)', 'weight' => '900', 'family' => 'Roboto, sans-serif' ],
        ];
        $vietnamese_fonts = apply_filters( 'nguu_lai_vietnamese_fonts', $vietnamese_fonts );

        $google_client_id = get_option( 'nguu_lai_google_client_id', '' );
        $initial_template = max( 0, min( count( $templates ) - 1, intval( $attributes['default_template'] ) - 1 ) );
        $initial_font     = sanitize_text_field( $attributes['default_font'] ?: 'Montserrat' );

        $js_data = [
            'lang'              => $lang,
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
            'i18n'              => $translations,
        ];

        // Enqueue Assets
        FrontendAssets::enqueue_workbench( $js_data );

        // Lấy Custom CSS từ Admin Settings
        $custom_css = get_option( 'nguu_lai_custom_css', '' );

        // Render template view
        ob_start();
        // Chèn Custom CSS trực tiếp vào HTML shortcode
        if ( ! empty( $custom_css ) ) :
            ?>
            <style id="nguu-lai-custom-css"><?php echo wp_strip_all_tags( $custom_css ); ?></style>
            <?php
        endif;
        $template_file = NGUU_LAI_PLUGIN_DIR . 'templates/frontend/meme-workbench.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        return (string) ob_get_clean();
    }
}
