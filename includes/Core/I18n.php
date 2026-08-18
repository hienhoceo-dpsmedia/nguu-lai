<?php
namespace NguuLai\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Quản lý ngôn ngữ và đa ngữ hóa (i18n) tự động cho /en, /zh, /vi.
 */
class I18n {

    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'nguu-lai',
            false,
            dirname( NGUU_LAI_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Nhận diện ngôn ngữ hiện tại đa tầng: Query param -> TranslatePress/WPML -> URL regex -> Locale.
     */
    public static function get_current_language(): string {
        // TẦNG 0: Cho phép chuyển ngữ qua tham số URL (vd: ?lang=en, ?lang=zh)
        if ( ! empty( $_GET['lang'] ) ) {
            $query_lang = strtolower( sanitize_key( wp_unslash( $_GET['lang'] ) ) );
            if ( in_array( $query_lang, [ 'en', 'zh', 'vi' ], true ) ) {
                return $query_lang;
            }
        }

        // TẦNG 1: Tương thích plugin đa ngôn ngữ (TranslatePress / WPML / Polylang)
        if ( class_exists( 'TRP_Translate_Press' ) ) {
            global $TRP_LANGUAGE;
            if ( ! empty( $TRP_LANGUAGE ) ) {
                $lang = strtolower( substr( $TRP_LANGUAGE, 0, 2 ) );
                if ( in_array( $lang, [ 'en', 'zh', 'vi' ], true ) ) {
                    return $lang;
                }
            }
        }

        if ( defined( 'ICL_LANGUAGE_CODE' ) && in_array( ICL_LANGUAGE_CODE, [ 'en', 'zh', 'vi' ], true ) ) {
            return ICL_LANGUAGE_CODE;
        }

        if ( function_exists( 'pll_current_language' ) ) {
            $pll_lang = pll_current_language();
            if ( in_array( $pll_lang, [ 'en', 'zh', 'vi' ], true ) ) {
                return $pll_lang;
            }
        }

        // TẦNG 2: Nhận diện theo đường dẫn URL (/en/, /zh/, /zh-hans/, /zh-hant/...)
        if ( isset( $_SERVER['REQUEST_URI'] ) ) {
            $uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
            if ( preg_match( '#^/en(/|$|\?)#i', $uri ) || strpos( $uri, '/en/' ) !== false ) {
                return 'en';
            }
            if ( preg_match( '#^/zh(-[a-z]+)?(/|$|\?)#i', $uri ) || strpos( $uri, '/zh/' ) !== false || strpos( $uri, '/zh-hans/' ) !== false || strpos( $uri, '/zh-hant/' ) !== false ) {
                return 'zh';
            }
        }

        // TẦNG 3: Nhận diện theo Locale của WordPress
        $locale = get_locale();
        $lang   = strtolower( substr( $locale, 0, 2 ) );
        if ( in_array( $lang, [ 'en', 'zh', 'vi' ], true ) ) {
            return $lang;
        }

        // TẦNG 4: Fallback mặc định về Tiếng Việt
        return 'vi';
    }

    /**
     * Lấy toàn bộ từ điển bản địa hóa theo ngôn ngữ.
     */
    public static function get_translations( string $lang = 'vi' ): array {
        $dict = [
            'vi' => [
                'auth_unlimited'          => 'Không giới hạn',
                'auth_login_req'          => 'Đăng nhập để tải meme',
                'auth_daily_quota'        => 'Lượt tải hôm nay: {rem}/{limit}',
                'btn_google_signin'       => 'Đăng nhập Google',
                'section_index'           => 'D / 03',
                'section_title'           => 'Để Ngưu Lai Nói Thay Lời Bạn',
                'section_aside'           => 'Tạo meme PNG 900×900 cực nét.',
                'preview_label'           => 'Xem trước trực tiếp',
                'label_meme_text'         => 'Nội dung chữ của bạn',
                'placeholder_meme_text'   => 'Nhập câu thoại meme...',
                'default_text'            => 'Hả?',
                'label_font'              => 'Phông chữ (Hỗ trợ 100% Tiếng Việt)',
                'label_size'              => 'Cỡ chữ',
                'label_pos_x'             => 'Vị trí ngang (X)',
                'label_pos_y'             => 'Vị trí dọc (Y)',
                'label_watermark'         => 'Watermark',
                'btn_upload'              => 'Tải ảnh lên',
                'btn_random_phrase'       => 'Đổi câu ngẫu nhiên',
                'btn_download'            => 'Tải meme về máy',
                'note_local_process'      => 'Xử lý 100% trên máy của bạn, không lưu máy chủ.',
                'templates_title'         => 'Biểu cảm Ngưu Lai',
                'templates_desc'          => '16 mẫu chuẩn nét, đổi ảnh giữ nguyên chữ.',
                'tpl_badge_active'        => 'Đang chọn',
                'tpl_badge_use'           => 'Dùng mẫu này',
                'phrases_title'           => 'Câu thoại gợi ý',
                'phrases_desc'            => 'Chạm để áp dụng câu thoại nhanh.',
                'modal_login_title'       => 'Đăng nhập với Google',
                'modal_login_desc_req'    => 'Trang yêu cầu đăng nhập tài khoản Google để tạo và tải meme không giới hạn!',
                'modal_login_desc_quota'  => 'Bạn đã dùng hết số lượt tải miễn phí hôm nay. Đăng nhập để tiếp tục tải meme không giới hạn!',
                'modal_perk_hd'           => 'Tải ảnh PNG 900 × 900 sắc nét',
                'modal_perk_unlimited'    => 'Không giới hạn số lượt tải mỗi ngày',
                'modal_security_note'     => 'Xác thực 1 chạm an toàn bởi Google Identity Services',
                'toast_download_ready'    => 'Đã tải meme thành công! 🎉',
                'toast_phrase_applied'    => 'Đã áp dụng câu thoại!',
                'toast_upload_success'    => 'Đã tải ảnh của bạn lên thành công!',
                'toast_quota_exceeded'    => 'Đã hết lượt tải miễn phí hôm nay!',
                'toast_login_success'     => 'Đăng nhập Google thành công! 🎉',
                'toast_login_failed'      => 'Đăng nhập thất bại. Vui lòng thử lại!',
            ],
            'en' => [
                'auth_unlimited'          => 'Unlimited',
                'auth_login_req'          => 'Sign in to download',
                'auth_daily_quota'        => 'Today: {rem}/{limit}',
                'btn_google_signin'       => 'Sign in with Google',
                'section_index'           => 'D / 03',
                'section_title'           => 'Let Niulai Speak For You',
                'section_aside'           => 'Create HD 900×900 PNG memes.',
                'preview_label'           => 'Live Preview',
                'label_meme_text'         => 'Your Meme Text',
                'placeholder_meme_text'   => 'Type your meme quote here...',
                'default_text'            => 'Excuse me?',
                'label_font'              => 'Typography Font',
                'label_size'              => 'Font Size',
                'label_pos_x'             => 'Horizontal Position (X)',
                'label_pos_y'             => 'Vertical Position (Y)',
                'label_watermark'         => 'Watermark',
                'btn_upload'              => 'Upload Image',
                'btn_random_phrase'       => 'Random Phrase',
                'btn_download'            => 'Download Meme',
                'note_local_process'      => '100% processed in your browser, no server storage.',
                'templates_title'         => 'Niulai Expressions',
                'templates_desc'          => '16 HD templates, change image and keep text.',
                'tpl_badge_active'        => 'Selected',
                'tpl_badge_use'           => 'Use this template',
                'phrases_title'           => 'Viral Phrases',
                'phrases_desc'            => 'Tap to instantly apply a phrase.',
                'modal_login_title'       => 'Sign in with Google',
                'modal_login_desc_req'    => 'Please sign in with Google to create and download unlimited memes!',
                'modal_login_desc_quota'  => 'You reached your free daily limit. Sign in to continue downloading without limits!',
                'modal_perk_hd'           => 'Download crisp 900 × 900 PNG images',
                'modal_perk_unlimited'    => 'Unlimited daily meme downloads',
                'modal_security_note'     => 'Secure 1-tap authentication by Google Identity Services',
                'toast_download_ready'    => 'Meme downloaded successfully! 🎉',
                'toast_phrase_applied'    => 'Phrase applied!',
                'toast_upload_success'    => 'Image uploaded successfully!',
                'toast_quota_exceeded'    => 'Daily free limit reached!',
                'toast_login_success'     => 'Google Sign-in successful! 🎉',
                'toast_login_failed'      => 'Sign-in failed. Please try again!',
            ],
            'zh' => [
                'auth_unlimited'          => '无限下载',
                'auth_login_req'          => '登录以无限下载',
                'auth_daily_quota'        => '今日下载: {rem}/{limit}',
                'btn_google_signin'       => '使用 Google 登录',
                'section_index'           => 'D / 03',
                'section_title'           => '让牛莱为你发声',
                'section_aside'           => '生成 900×900 超清 PNG 梗图。',
                'preview_label'           => '实时预览',
                'label_meme_text'         => '输入你的梗图文字',
                'placeholder_meme_text'   => '输入搞笑台词/金句...',
                'default_text'            => '你说什么？',
                'label_font'              => '梗图字体选择',
                'label_size'              => '字体大小',
                'label_pos_x'             => '水平位置 (X)',
                'label_pos_y'             => '垂直位置 (Y)',
                'label_watermark'         => '水印',
                'btn_upload'              => '上传自定义图片',
                'btn_random_phrase'       => '随机金句',
                'btn_download'            => '下载梗图',
                'note_local_process'      => '100% 本地浏览器渲染生成，不存储于服务器。',
                'templates_title'         => '牛莱表情包 (16款)',
                'templates_desc'          => '16款超清原版表情，切换模板文字保留。',
                'tpl_badge_active'        => '当前选择',
                'tpl_badge_use'           => '使用此模板',
                'phrases_title'           => '精选搞笑金句',
                'phrases_desc'            => '点击直接应用热门金句。',
                'modal_login_title'       => '使用 Google 登录',
                'modal_login_desc_req'    => '请登录 Google 账号以享受无限次制作与下载梗图！',
                'modal_login_desc_quota'  => '您今日的免费下载额度已用完。登录 Google 即可无限制畅享下载！',
                'modal_perk_hd'           => '极速导出 900 × 900 高清无损 PNG',
                'modal_perk_unlimited'    => '每日下载次数无上限',
                'modal_security_note'     => 'Google Identity Services 官方一键安全认证',
                'toast_download_ready'    => '梗图下载成功！🎉',
                'toast_phrase_applied'    => '已应用该金句！',
                'toast_upload_success'    => '自定义图片上传成功！',
                'toast_quota_exceeded'    => '今日免费下载次数已用尽！',
                'toast_login_success'     => 'Google 登录成功！🎉',
                'toast_login_failed'      => '登录失败，请稍后重试！',
            ],
        ];

        return $dict[ $lang ] ?? $dict['vi'];
    }

    /**
     * Lấy danh sách câu thoại viral phù hợp theo ngôn ngữ.
     */
    public static function get_phrases_for_lang( string $lang = 'vi' ): array {
        if ( 'en' === $lang ) {
            return [
                'Excuse me?',
                'Are you serious right now?',
                'Plot twist: nobody cares.',
                'Stay calm and don\'t panic.',
                'Say that again, I dare you.',
                'When you finally understand the assignment.',
                'Bro really thought he did something.',
                'I came, I saw, I went back to sleep.',
                'Can we just pretend this didn\'t happen?',
                'My brain has too many tabs open.',
                'That was definitely not on my bingo card.',
                'Legend says he\'s still waiting.',
                'I am confusion.',
                'It is what it is.',
                'Wait, that\'s illegal.',
                'Mood 24/7.',
                'Send help immediately.',
                'I have no idea what is going on.',
                'Living my best life.',
                'Absolutely unhinged behavior.',
            ];
        }

        if ( 'zh' === $lang ) {
            return [
                '你说什么？',
                '大无语事件。',
                '我听到了，但我装作不知道。',
                '别搞我啊兄弟！',
                '真有你的。',
                '我太难了！',
                '格局打开。',
                '你认真的吗？',
                '冷静一下，千万别慌。',
                '小丑竟是我自己。',
                '尊嘟假嘟？',
                '退！退！退！',
                '蚌埠住了。',
                '牛莱：我就静静看着你。',
                '今天的班就上到这里。',
                'CPU烧了。',
                '破防了家人们。',
                '听君一席话，如听一席话。',
                '有请下一位受害者。',
                '你清高，你了不起！',
            ];
        }

        // Tiếng Việt (lấy từ tùy chọn admin nếu có)
        $custom = get_option( 'nguu_lai_default_phrases', [] );
        if ( ! empty( $custom ) && is_array( $custom ) ) {
            return $custom;
        }

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
}
