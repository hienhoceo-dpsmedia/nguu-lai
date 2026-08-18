<?php
namespace NguuLai\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Xử lý khi kích hoạt Plugin (Tạo bảng Database & Thiết lập Tùy chọn mặc định).
 */
class Activator {

    public static function activate(): void {
        \NguuLai\Models\Database::create_or_migrate_tables();

        // Bộ Câu thoại tiếng Việt viral thịnh hành cho Ngưu Lai
        $default_phrases = [
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

        // Khởi tạo các tùy chọn mặc định nếu chưa tồn tại
        if ( false === get_option( 'nguu_lai_default_phrases' ) ) {
            update_option( 'nguu_lai_default_phrases', $default_phrases );
        }

        if ( false === get_option( 'nguu_lai_watermark_text' ) ) {
            update_option( 'nguu_lai_watermark_text', 'niulai.wiki' );
        }

        if ( false === get_option( 'nguu_lai_watermark_enabled' ) ) {
            update_option( 'nguu_lai_watermark_enabled', '1' );
        }

        if ( false === get_option( 'nguu_lai_guest_quota' ) ) {
            update_option( 'nguu_lai_guest_quota', 5 );
        }

        if ( false === get_option( 'nguu_lai_require_login' ) ) {
            update_option( 'nguu_lai_require_login', '0' );
        }

        if ( false === get_option( 'nguu_lai_trust_proxies' ) ) {
            update_option( 'nguu_lai_trust_proxies', '1' );
        }
    }
}
