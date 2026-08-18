<?php
/**
 * Template Giao diện Meme Workbench 100% Thuần Việt (Modal Đăng nhập Tối ưu Mobile & Desktop).
 *
 * @package NguuLai
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id          = get_current_user_id();
$user             = wp_get_current_user();
$is_logged_in     = $user_id > 0;
$avatar_url       = '';
if ( $is_logged_in ) {
    $avatar_url = get_user_meta( $user_id, 'nguu_lai_google_avatar', true ) ?: get_avatar_url( $user_id, [ 'size' => 48 ] );
}

$templates        = ! empty( $templates ) ? $templates : [];
$phrases          = ! empty( $phrases ) ? $phrases : [];
$vietnamese_fonts = ! empty( $vietnamese_fonts ) ? $vietnamese_fonts : [];
$initial_template = isset( $initial_template ) ? $initial_template : 0;
$initial_font     = isset( $initial_font ) ? $initial_font : 'Montserrat';
$default_text     = isset( $attributes['default_text'] ) ? $attributes['default_text'] : 'Hả?';
$watermark_text   = isset( $watermark_text ) ? $watermark_text : 'niulai.wiki';
$watermark_on     = isset( $watermark_enabled ) ? $watermark_enabled : true;
$quota            = isset( $quota_status ) ? $quota_status : [ 'remaining_quota' => 5, 'daily_limit' => 5, 'require_login' => false ];
?>

<div class="nguu-lai-container" id="nguu-lai-workbench-wrap">
    
    <!-- Dữ liệu Cấu hình JSON nhúng trực tiếp an toàn -->
    <?php if ( ! empty( $js_data ) ) : ?>
        <script id="nguu-lai-config-json" type="application/json">
            <?php echo wp_json_encode( $js_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
        </script>
    <?php endif; ?>

    <section class="meme-section section-shell" id="meme">
        
        <!-- Header Thanh Trạng thái Người dùng & Đăng nhập Google -->
        <div class="nguu-lai-auth-bar" id="nguu-lai-auth-bar">
            <div class="auth-bar-left">
                <?php if ( $is_logged_in ) : ?>
                    <div class="user-pill is-vip">
                        <?php if ( ! empty( $avatar_url ) ) : ?>
                            <img src="<?php echo esc_url( $avatar_url ); ?>" alt="Avatar" class="user-avatar" />
                        <?php endif; ?>
                        <span class="user-name"><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></span>
                        <span class="badge-unlimited">
                            <svg class="svg-inline-icon" viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M12 2l2.4 7.2L22 12l-7.6 2.8L12 22l-2.4-7.2L2 12l7.6-2.8L12 2z"/></svg>
                            Lượt tải: Không giới hạn
                        </span>
                    </div>
                <?php else : ?>
                    <div class="guest-pill" id="guest-quota-pill">
                        <span class="guest-icon">
                            <svg class="svg-inline-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3l1 4h3a2 2 0 0 1 2 2v2a3 3 0 0 1-3 3h-1l-1 5a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2l-1-5H5a3 3 0 0 1-3-3V9a2 2 0 0 1 2-2h3l1-4h8z"/><circle cx="9" cy="11" r="1.5" fill="currentColor"/><circle cx="15" cy="11" r="1.5" fill="currentColor"/><path d="M10 16h4"/></svg>
                        </span>
                        <span class="guest-text" id="quota-text-display">
                            <?php if ( ! empty( $quota['require_login'] ) ) : ?>
                                Bắt buộc đăng nhập Google để tải meme
                            <?php else : ?>
                                Lượt tải miễn phí hôm nay: <?php echo esc_html( (string) $quota['remaining_quota'] ); ?>/<?php echo esc_html( (string) $quota['daily_limit'] ); ?> lượt
                            <?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="auth-bar-right">
                <?php if ( ! $is_logged_in ) : ?>
                    <div id="google-signin-btn-container"></div>
                    <button type="button" class="btn-google-trigger" id="btn-trigger-google-login">
                        <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/>
                            <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                            <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.8s.2-2.1.4-2.8L1.9 6.3C.7 8.7 0 10.8 0 12s.7 3.3 1.9 5.7l3.7-2.9z"/>
                            <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2.4-6.4-5.2L1.9 16c1.8 3.7 5.6 7 10.1 7z"/>
                        </svg>
                        <span>Đăng nhập Google</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tiêu đề Khối Meme -->
        <div class="section-heading section-heading-row">
            <div>
                <p class="section-index">D / 03</p>
                <h2><?php echo esc_html( $attributes['title'] ?? 'Để Ngưu Lai Nói Thay Lời Bạn.' ); ?></h2>
            </div>
            <p class="section-aside"><?php echo esc_html( $attributes['aside'] ?? 'Chọn một biểu cảm, thêm một dòng chữ, căn chỉnh trực tiếp và xuất ảnh PNG chuẩn 900 × 900.' ); ?></p>
        </div>

        <!-- Bàn Làm Việc Meme (Workbench) -->
        <div class="meme-workbench">
            <div class="meme-preview-wrap">
                <p class="preview-label">Xem trước trực tiếp</p>
                <canvas id="meme-canvas" width="900" height="900" aria-label="Trình xem trước meme Ngưu Lai thời gian thực"></canvas>
            </div>
            
            <div class="meme-controls">
                <!-- Nhập Nội dung chữ -->
                <label class="tool-field">
                    <span>Nội dung chữ của bạn</span>
                    <input id="meme-text" type="text" maxlength="42" value="<?php echo esc_attr( $default_text ); ?>" placeholder="Nhập câu thoại meme..." autocomplete="off" />
                </label>
                
                <!-- Bộ Chọn Phông Chữ (100% Chuẩn Tiếng Việt) -->
                <label class="tool-field select-field">
                    <span>Phông chữ (Hỗ trợ 100% Tiếng Việt)</span>
                    <div class="select-wrapper">
                        <select id="meme-font">
                            <?php foreach ( $vietnamese_fonts as $f ) : ?>
                                <option value="<?php echo esc_attr( $f['name'] ); ?>" <?php selected( $f['name'], $initial_font ); ?>>
                                    <?php echo esc_html( $f['label'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </label>

                <!-- Kích thước chữ -->
                <label class="tool-field range-field">
                    <span>
                        <span>Cỡ chữ</span>
                        <output id="meme-size-output">64px</output>
                    </span>
                    <input id="meme-size" type="range" min="28" max="120" value="64" />
                </label>
                
                <!-- Vị trí X -->
                <label class="tool-field range-field">
                    <span>
                        <span>Vị trí ngang (X)</span>
                        <output id="meme-x-output">50%</output>
                    </span>
                    <input id="meme-x" type="range" min="8" max="92" value="50" />
                </label>
                
                <!-- Vị trí Y -->
                <label class="tool-field range-field">
                    <span>
                        <span>Vị trí dọc (Y)</span>
                        <output id="meme-y-output">82%</output>
                    </span>
                    <input id="meme-y" type="range" min="8" max="92" value="82" />
                </label>
                
                <!-- Tùy chọn Watermark & Upload -->
                <div class="meme-option-row">
                    <label class="checkbox-label">
                        <input id="meme-watermark" type="checkbox" <?php checked( $watermark_on ); ?> />
                        <span id="meme-watermark-label">Giữ watermark <?php echo esc_html( $watermark_text ); ?></span>
                    </label>
                    <label class="upload-button">
                        <input id="meme-upload" type="file" accept="image/*" />
                        <span class="upload-btn-content">
                            <svg class="svg-inline-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            Tải ảnh của bạn lên
                        </span>
                    </label>
                </div>
                
                <!-- Nút Hành động -->
                <div class="meme-actions">
                    <button class="button button-quiet" id="meme-random" type="button">
                        <svg class="svg-inline-icon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="3" ry="3"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><circle cx="15.5" cy="8.5" r="1.5" fill="currentColor"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/><circle cx="8.5" cy="15.5" r="1.5" fill="currentColor"/><circle cx="15.5" cy="15.5" r="1.5" fill="currentColor"/></svg>
                        <span>Đổi câu ngẫu nhiên</span>
                    </button>
                    <button class="button button-dark" id="meme-download" type="button">
                        <span>Tải meme về máy</span>
                        <span aria-hidden="true">↓</span>
                    </button>
                </div>
                
                <p class="meme-local-note">
                    <svg class="svg-inline-icon" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ảnh được xử lý 100% an toàn trực tiếp trên trình duyệt của bạn và không tải lên máy chủ.
                </p>
            </div>
        </div>

        <!-- Thư viện 16 Mẫu Ngưu Lai Chuẩn (Hiển thị ngay trên UI) -->
        <div class="meme-library">
            <div class="meme-library-head">
                <h3>Chọn một biểu cảm Ngưu Lai</h3>
                <p>16 mẫu biểu cảm chuẩn nét. Đổi ảnh vẫn giữ nguyên câu chữ đang viết.</p>
            </div>
            <div class="meme-template-grid" id="meme-template-grid">
                <?php foreach ( $templates as $index => $tpl_url ) : 
                    $is_active = ( $index === $initial_template );
                    $tpl_num   = $index + 1;
                ?>
                    <button type="button" 
                            class="<?php echo $is_active ? 'is-active' : ''; ?>" 
                            data-index="<?php echo esc_attr( $index ); ?>" 
                            data-src="<?php echo esc_url( $tpl_url ); ?>" 
                            aria-label="Chọn phôi Ngưu Lai số <?php echo esc_attr( $tpl_num ); ?>" 
                            aria-pressed="<?php echo $is_active ? 'true' : 'false'; ?>">
                        <img src="<?php echo esc_url( $tpl_url ); ?>" alt="Ngưu Lai Mẫu <?php echo esc_attr( $tpl_num ); ?>" loading="lazy" />
                        <span><?php echo $is_active ? 'Đang chọn' : 'Dùng mẫu này'; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Thư viện Gợi ý Câu thoại (Hiển thị ngay trên UI) -->
        <div class="phrase-library">
            <div class="meme-library-head">
                <h3>Gợi ý câu thoại hay</h3>
                <p>Bấm chọn câu bất kỳ để tự động điền ngay vào khung sửa.</p>
            </div>
            <div class="phrase-grid" id="phrase-grid">
                <?php foreach ( $phrases as $phrase_item ) : ?>
                    <button type="button" data-phrase="<?php echo esc_attr( $phrase_item ); ?>">
                        <?php echo esc_html( $phrase_item ); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

    </section>

    <!-- Modal Thông báo Đăng nhập Google (Tối ưu UI Mobile & Desktop) -->
    <div class="nguu-lai-modal" id="google-login-modal" hidden>
        <div class="nguu-lai-backdrop"></div>
        <div class="nguu-lai-dialog">
            <div class="nguu-lai-drag-handle"></div>
            <button type="button" class="nguu-lai-close-btn" id="modal-close-btn" aria-label="Đóng">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            
            <div class="modal-header-hero">
                <div class="modal-avatar-wrap">
                    <img src="<?php echo esc_url( NGUU_LAI_PLUGIN_URL . 'assets/memes/niulai_01.webp' ); ?>" alt="Ngưu Lai" class="modal-avatar-img" />
                    <span class="modal-avatar-badge">✨</span>
                </div>
                <h3 class="modal-title">Đăng nhập với Google</h3>
                <p class="modal-desc" id="modal-desc-text">
                    <?php if ( ! empty( $quota['require_login'] ) ) : ?>
                        Trang yêu cầu đăng nhập tài khoản Google để tạo và tải meme không giới hạn!
                    <?php else : ?>
                        Bạn đã dùng hết số lượt tải miễn phí hôm nay. Đăng nhập để tiếp tục tải meme không giới hạn!
                    <?php endif; ?>
                </p>
            </div>

            <!-- Danh sách Đặc quyền VIP khi Đăng nhập -->
            <div class="modal-perks-list">
                <div class="perk-item">
                    <span class="perk-icon">⚡</span>
                    <span>Tải ảnh PNG 900 × 900 sắc nét</span>
                </div>
                <div class="perk-item">
                    <span class="perk-icon">♾️</span>
                    <span>Không giới hạn số lượt tải mỗi ngày</span>
                </div>
            </div>

            <!-- Nút Google Sign-In chính thức -->
            <div class="modal-google-btn-slot" id="modal-google-btn-slot"></div>
            
            <div class="modal-security-note">
                <svg class="svg-inline-icon" viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>Xác thực 1 chạm an toàn bởi Google Identity Services</span>
            </div>
        </div>
    </div>

    <!-- Toast Thông Báo Thuần Việt -->
    <div class="nguu-lai-toast" id="nguu-lai-toast" role="status" aria-live="polite"></div>
</div>
