<?php
namespace NguuLai\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use NguuLai\Admin\Settings;
use NguuLai\Admin\AdminAssets;
use NguuLai\Frontend\Shortcode;
use NguuLai\Frontend\FrontendAssets;
use NguuLai\Api\RestController;

/**
 * Lớp điều phối trung tâm (Singleton) của Plugin Ngưu Lai.
 */
class Plugin {

    private static ?Plugin $instance = null;
    protected Loader $loader;

    public static function get_instance(): Plugin {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->loader = new Loader();
        $this->check_migrations();
        $this->set_locale();
        $this->define_api_hooks();
        $this->define_frontend_hooks();

        if ( is_admin() ) {
            $this->define_admin_hooks();
        }
    }

    private function check_migrations(): void {
        // Tự động cập nhật danh sách câu thoại viral mới nhất nếu chưa nâng cấp phiên bản câu thoại
        if ( get_option( 'nguu_lai_phrases_version' ) !== '2.2' ) {
            $viral_phrases = [
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
            update_option( 'nguu_lai_default_phrases', $viral_phrases );
            update_option( 'nguu_lai_phrases_version', '2.2' );
        }
    }

    private function set_locale(): void {
        $i18n = new I18n();
        $this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
    }

    private function define_frontend_hooks(): void {
        $frontend_assets = new FrontendAssets();
        $this->loader->add_action( 'wp_enqueue_scripts', $frontend_assets, 'register_assets' );

        $shortcode = new Shortcode();
        $this->loader->add_action( 'init', $shortcode, 'register_shortcode' );
    }

    private function define_api_hooks(): void {
        $rest_controller = new RestController();
        $this->loader->add_action( 'rest_api_init', $rest_controller, 'register_routes' );
    }

    private function define_admin_hooks(): void {
        $admin_settings = new Settings();
        $admin_assets   = new AdminAssets();

        $this->loader->add_action( 'admin_menu', $admin_settings, 'register_admin_menu' );
        $admin_settings->register_post_handlers();

        $this->loader->add_action( 'admin_enqueue_scripts', $admin_assets, 'enqueue_assets' );
    }

    public function run(): void {
        $this->loader->run();
    }
}
