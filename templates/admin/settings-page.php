<?php
/**
 * Giao diện Quản trị Plugin Ngưu Lai 100% Thuần Việt.
 *
 * @package NguuLai
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap nguu-lai-admin-wrap">
    <div class="nguu-lai-admin-header">
        <div class="header-left">
            <h1 class="admin-title">🐮 Ngưu Lai — Trình Tạo Meme & Quản Trị</h1>
            <p class="admin-subtitle">Tùy biến shortcode meme, quản lý Google OAuth, thiết lập hạn mức và theo dõi nhật ký thời gian thực.</p>
        </div>
        <div class="header-right">
            <span class="version-badge">Phiên bản <?php echo esc_html( NGUU_LAI_VERSION ); ?></span>
        </div>
    </div>

    <!-- Thông báo Trạng thái (Notices) -->
    <?php if ( ! empty( $status ) ) : ?>
        <?php if ( in_array( $status, [ 'saved', 'settings_saved' ], true ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>✅ Đã lưu cấu hình cài đặt thành công!</p></div>
        <?php elseif ( 'phrases_saved' === $status ) : ?>
            <div class="notice notice-success is-dismissible"><p>✅ Đã cập nhật danh sách câu thoại gợi ý thành công!</p></div>
        <?php elseif ( 'phrases_reset' === $status ) : ?>
            <div class="notice notice-success is-dismissible"><p>🔄 Đã khôi phục toàn bộ danh sách câu thoại viral mặc định thành công!</p></div>
        <?php elseif ( 'ip_blocked' === $status ) : ?>
            <div class="notice notice-success is-dismissible"><p>🚫 Đã thêm địa chỉ IP vào danh sách chặn thành công!</p></div>
        <?php elseif ( 'ip_unblocked' === $status ) : ?>
            <div class="notice notice-success is-dismissible"><p>🔓 Đã mở chặn địa chỉ IP thành công!</p></div>
        <?php elseif ( in_array( $status, [ 'blocklist_cleared', 'all_blocks_cleared' ], true ) ) : ?>
            <div class="notice notice-success is-dismissible"><p>✨ Đã xóa sạch danh sách IP bị chặn!</p></div>
        <?php elseif ( 'logs_cleared' === $status ) : ?>
            <div class="notice notice-success is-dismissible"><p>🗑️ Đã xóa sạch toàn bộ nhật ký tạo meme!</p></div>
        <?php endif; ?>
    <?php endif; ?>


    <!-- Thanh Điều Hướng Tabs -->
    <nav class="nav-tab-wrapper nguu-lai-nav-tabs">
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'overview' ], admin_url( 'admin.php' ) ) ); ?>" 
           class="nav-tab <?php echo 'overview' === $current_tab ? 'nav-tab-active' : ''; ?>">
           📌 Tổng quan & Shortcode
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'settings' ], admin_url( 'admin.php' ) ) ); ?>" 
           class="nav-tab <?php echo 'settings' === $current_tab ? 'nav-tab-active' : ''; ?>">
           ⚙️ Cài đặt Google & Hạn mức
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'phrases' ], admin_url( 'admin.php' ) ) ); ?>" 
           class="nav-tab <?php echo 'phrases' === $current_tab ? 'nav-tab-active' : ''; ?>">
           💬 Quản lý Câu thoại & Mẫu
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'logs' ], admin_url( 'admin.php' ) ) ); ?>" 
           class="nav-tab <?php echo 'logs' === $current_tab ? 'nav-tab-active' : ''; ?>">
           📊 Nhật ký & Thống kê
        </a>
        <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'security' ], admin_url( 'admin.php' ) ) ); ?>" 
           class="nav-tab <?php echo 'security' === $current_tab ? 'nav-tab-active' : ''; ?>">
           🛡️ Bảo mật & Chặn IP
        </a>
    </nav>

    <div class="nguu-lai-tab-content">
        
        <!-- ===================================================================
             TAB 1: TỔNG QUAN & SHORTCODE
             =================================================================== -->
        <?php if ( 'overview' === $current_tab ) : ?>
            <div class="nguu-lai-card">
                <div class="card-header">
                    <h2>Cách Nhúng Trình Tạo Meme Vào Website</h2>
                    <p>Sao chép đoạn mã ngắn (Shortcode) dưới đây và dán vào bất kỳ bài viết, trang tĩnh (Page), widget hoặc trình dựng trang nào (Elementor, Gutenberg, Divi, Bricks).</p>
                </div>
                <div class="shortcode-copy-box">
                    <input type="text" readonly value="[nguu_lai_meme]" id="nguu-lai-shortcode-input" />
                    <button type="button" class="button button-primary" id="btn-copy-shortcode">Sao chép mã 📋</button>
                </div>
            </div>

            <div class="nguu-lai-card">
                <div class="card-header">
                    <h2>Danh Sách Thuộc Tính Tùy Biến Nâng Cao</h2>
                </div>
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th style="width: 22%;">Thuộc tính</th>
                            <th style="width: 18%;">Mặc định</th>
                            <th>Mô tả chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>default_text</code></td>
                            <td><code>"Hả?"</code></td>
                            <td>Nội dung câu thoại hiển thị sẵn ban đầu trên khung Canvas.</td>
                        </tr>
                        <tr>
                            <td><code>default_template</code></td>
                            <td><code>1</code></td>
                            <td>Số thứ tự phôi meme hiển thị ban đầu (chọn từ 1 đến 16).</td>
                        </tr>
                        <tr>
                            <td><code>watermark</code></td>
                            <td><code>"1"</code></td>
                            <td>Bật (<code>"1"</code>) hoặc tắt (<code>"0"</code>) checkbox watermark bản quyền góc dưới ảnh.</td>
                        </tr>
                        <tr>
                            <td><code>watermark_text</code></td>
                            <td><code>"niulai.wiki"</code></td>
                            <td>Nội dung chữ in watermark bản quyền.</td>
                        </tr>
                        <tr>
                            <td><code>require_login</code></td>
                            <td><code>"0"</code></td>
                            <td>Bắt buộc đăng nhập Google mới được tạo/tải meme (<code>"1"</code> là bắt buộc, <code>"0"</code> là cho phép khách tải miễn phí có giới hạn).</td>
                        </tr>
                        <tr>
                            <td><code>title</code></td>
                            <td><code>"Để Ngưu Lai Nói Thay Lời Bạn."</code></td>
                            <td>Tiêu đề chính hiển thị phía trên Bàn làm việc Meme.</td>
                        </tr>
                    </tbody>
                </table>
                <div style="margin-top: 15px;">
                    <p><strong>Ví dụ mẫu nhúng nâng cao:</strong></p>
                    <code>[nguu_lai_meme default_text="Ủa alo?" default_template="2" watermark="1" require_login="0"]</code>
                </div>
            </div>

        <!-- ===================================================================
             TAB 2: CÀI ĐẶT GOOGLE & HẠN MỨC
             =================================================================== -->
        <?php elseif ( 'settings' === $current_tab ) : ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="nguu_lai_save_settings" />
                <?php wp_nonce_field( 'nguu_lai_save_settings_action', 'nguu_lai_nonce' ); ?>

                <div class="nguu-lai-card">
                    <div class="card-header">
                        <h2>1. Cấu Hình Đăng Nhập Google (Google OAuth)</h2>
                        <p>Kích hoạt tính năng đăng nhập Google One-Tap và nút đăng nhập 1 chạm để quản lý người dùng và cấp lượt tải không giới hạn.</p>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="nguu_lai_google_client_id">Google Client ID</label></th>
                            <td>
                                <input type="text" name="nguu_lai_google_client_id" id="nguu_lai_google_client_id" 
                                       value="<?php echo esc_attr( $google_client_id ); ?>" class="regular-text" placeholder="xxxx-xxxx.apps.googleusercontent.com" />
                                <p class="description">Lấy Client ID từ <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>. Đảm bảo đã thêm tên miền website của bạn vào mục <em>Authorized JavaScript origins</em>.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Bắt buộc Đăng nhập</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="nguu_lai_require_login" value="1" <?php checked( $require_login ); ?> />
                                    Bắt buộc người dùng phải đăng nhập Google mới được phép tải meme về máy
                                </label>
                                <p class="description">Nếu không tích, khách vãng lai vẫn được tải một số lượt miễn phí mỗi ngày trước khi yêu cầu đăng nhập.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="nguu_lai_guest_quota">Hạn mức Khách vãng lai</label></th>
                            <td>
                                <input type="number" name="nguu_lai_guest_quota" id="nguu_lai_guest_quota" 
                                       value="<?php echo esc_attr( $guest_quota ); ?>" min="1" max="100" class="small-text" /> lượt tải / ngày
                                <p class="description">Số lượng meme tối đa mà một địa chỉ IP khách chưa đăng nhập có thể tải trong 1 ngày.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="nguu-lai-card">
                    <div class="card-header">
                        <h2>2. Cấu Hình Watermark & Bản Quyền</h2>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="nguu_lai_watermark_text">Chữ Watermark Mặc định</label></th>
                            <td>
                                <input type="text" name="nguu_lai_watermark_text" id="nguu_lai_watermark_text" 
                                       value="<?php echo esc_attr( $watermark_text ); ?>" class="regular-text" />
                                <p class="description">Chữ in mờ góc phải dưới của hình ảnh meme xuất ra (ví dụ: <code>niulai.wiki</code> hoặc tên miền của bạn).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Trạng thái Watermark</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="nguu_lai_watermark_enabled" value="1" <?php checked( $watermark_enabled ); ?> />
                                    Mặc định tích chọn bật watermark khi người dùng mở trình tạo meme
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="nguu-lai-card">
                    <div class="card-header">
                        <h2>3. Cấu Hình Mạng & Nhận Diện IP</h2>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Tin cậy Cloudflare / Proxy</th>
                            <td>
                                <label>
                                    <input type="checkbox" name="nguu_lai_trust_proxies" value="1" <?php checked( $trust_proxies ); ?> />
                                    Bật tự động đọc IP thật qua header <code>HTTP_CF_CONNECTING_IP</code> và <code>HTTP_X_FORWARDED_FOR</code>
                                </label>
                                <p class="description">Giúp tính toán hạn mức lượt tải chính xác khi website của bạn sử dụng Cloudflare hoặc Reverse Proxy.</p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="nguu-lai-card">
                    <div class="card-header">
                        <h2>4. Custom CSS Tùy Chỉnh (Giao Diện Shortcode)</h2>
                    </div>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="nguu_lai_custom_css">CSS tùy chỉnh</label>
                            </th>
                            <td>
                                <textarea id="nguu_lai_custom_css" name="nguu_lai_custom_css" rows="12" class="large-text code" style="font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; line-height: 1.5; padding: 12px; border-radius: 6px; background: #1e1e2e; color: #cdd6f4; border: 1px solid #45475a;"><?php echo esc_textarea( $custom_css ); ?></textarea>
                                <p class="description">
                                    💡 CSS ở đây sẽ được tự động chèn vào trang chứa shortcode <code>[nguu_lai_meme]</code>. Dùng để tùy chỉnh giao diện mà không cần sửa file plugin.<br>
                                    <strong>Ví dụ:</strong> <code>.nguu-lai-workbench { border-radius: 20px; }</code>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <button type="submit" class="button button-primary button-hero">Lưu Toàn Bộ Cài Đặt 💾</button>
                </p>
            </form>

        <!-- ===================================================================
             TAB 3: QUẢN LÝ CÂU THOẠI & MẪU PHÔI
             =================================================================== -->
        <?php elseif ( 'phrases' === $current_tab ) : ?>
            <div class="nguu-lai-card">
                <div class="card-header">
                    <h2>Quản Lý Danh Sách Câu Thoại Gợi Ý (Phrase Library)</h2>
                    <p>Các câu thoại này sẽ xuất hiện dưới dạng các nút bấm nhanh trên giao diện tạo meme giúp người dùng chọn chỉ bằng 1 chạm.</p>
                </div>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="nguu_lai_save_phrases" />
                    <?php wp_nonce_field( 'nguu_lai_save_phrases_action', 'nguu_lai_nonce' ); ?>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="nguu_lai_phrases_text">Danh sách câu thoại<br><small style="font-weight: normal; color: #888;">(Mỗi dòng là 1 câu thoại)</small></label>
                            </th>
                            <td>
                                <textarea id="nguu_lai_phrases_text" name="nguu_lai_phrases_text" rows="16" class="large-text code" style="font-size: 14px; line-height: 1.6; font-family: 'Be Vietnam Pro', 'Montserrat', sans-serif; padding: 12px; border-radius: 6px;"><?php echo esc_textarea( implode( "\n", (array) $phrases ) ); ?></textarea>
                                <p class="description">
                                    💡 <strong>Mẹo:</strong> Nhập mỗi câu thoại trên một dòng riêng biệt. Hiện tại có <strong><?php echo esc_html( (string) count( (array) $phrases ) ); ?></strong> câu thoại đang hoạt động.
                                </p>
                            </td>
                        </tr>
                    </table>

                    <div style="display: flex; gap: 12px; align-items: center; margin-top: 14px;">
                        <button type="submit" class="button button-primary button-hero">Lưu Danh Sách Câu Thoại 💬</button>
                    </div>
                </form>

                <hr style="margin: 24px 0 18px; border: 0; border-top: 1px solid #eee;" />

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <strong>Khôi phục mặc định:</strong> Nhấn để tải lại toàn bộ danh sách các câu nói viral hot trend mới nhất.
                    </div>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục toàn bộ danh sách câu thoại viral mặc định? Các câu tự nhập thêm sẽ được thay thế bằng danh sách chuẩn.');">
                        <input type="hidden" name="action" value="nguu_lai_reset_phrases" />
                        <?php wp_nonce_field( 'nguu_lai_reset_phrases_action', 'nguu_lai_nonce' ); ?>
                        <button type="submit" class="button button-secondary">🔄 Khôi Phục Danh Sách Viral Mặc Định</button>
                    </form>
                </div>
            </div>


            <div class="nguu-lai-card">
                <div class="card-header">
                    <h2>Xem Trước 16 Phôi Ảnh Biểu Cảm Ngưu Lai</h2>
                    <p>Các phôi ảnh WebP chuẩn được tối ưu siêu nhẹ lưu trữ tại <code>assets/memes/</code>.</p>
                </div>
                <div class="admin-template-preview-grid">
                    <?php for ( $i = 1; $i <= 16; $i++ ) : 
                        $num = str_pad( (string) $i, 2, '0', STR_PAD_LEFT );
                        $url = NGUU_LAI_PLUGIN_URL . "assets/memes/niulai_{$num}.webp";
                    ?>
                        <div class="template-thumb-card">
                            <img src="<?php echo esc_url( $url ); ?>" alt="Mẫu <?php echo esc_attr( $num ); ?>" />
                            <span>Mẫu <?php echo esc_html( $num ); ?></span>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

        <!-- ===================================================================
             TAB 4: NHẬT KÝ & THỐNG KÊ (ANALYTICS)
             =================================================================== -->
        <?php elseif ( 'logs' === $current_tab ) : ?>
            
            <!-- Thẻ KPI Thống kê -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <span class="kpi-title">TỔNG SỐ MEME ĐÃ TẠO</span>
                    <strong class="kpi-value"><?php echo number_format_i18n( $analytics['total_memes'] ); ?></strong>
                    <span class="kpi-desc">Tổng lượt tải thành công</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-title">ĐỊA CHỈ IP TRUY CẬP</span>
                    <strong class="kpi-value"><?php echo number_format_i18n( $analytics['unique_ips'] ); ?></strong>
                    <span class="kpi-desc">Số IP độc lập đã dùng công cụ</span>
                </div>
                <div class="kpi-card">
                    <span class="kpi-title">NGƯỜI DÙNG GOOGLE</span>
                    <strong class="kpi-value"><?php echo number_format_i18n( $analytics['unique_users'] ); ?></strong>
                    <span class="kpi-desc">Thành viên đã đăng nhập</span>
                </div>
            </div>

            <!-- Top Templates Được Yêu Thích -->
            <?php if ( ! empty( $analytics['top_templates'] ) ) : ?>
                <div class="nguu-lai-card">
                    <div class="card-header">
                        <h2>Top Mẫu Phôi Được Sử Dụng Nhiều Nhất</h2>
                    </div>
                    <div class="top-templates-list">
                        <?php foreach ( $analytics['top_templates'] as $top_item ) : ?>
                            <div class="top-item-badge">
                                <strong><?php echo esc_html( $top_item->template ); ?>:</strong>
                                <span><?php echo number_format_i18n( (int) $top_item->total_count ); ?> lượt</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bộ Lọc Nhật Ký -->
            <div class="nguu-lai-card">
                <div class="card-header-flex">
                    <h2>Danh Sách Nhật Ký Tạo Meme</h2>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ nhật ký tạo meme không?');">
                        <input type="hidden" name="action" value="nguu_lai_clear_all_logs" />
                        <?php wp_nonce_field( 'nguu_lai_clear_all_logs_action', 'nguu_lai_nonce' ); ?>
                        <button type="submit" class="button button-link-delete">🗑️ Xóa sạch nhật ký</button>
                    </form>
                </div>

                <!-- Thanh lọc ngày -->
                <form method="get" class="filter-form">
                    <input type="hidden" name="page" value="nguu-lai-settings" />
                    <input type="hidden" name="tab" value="logs" />
                    
                    <div class="filter-row">
                        <div class="filter-presets">
                            <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'logs', 'preset' => 'today' ], admin_url( 'admin.php' ) ) ); ?>" 
                               class="button <?php echo 'today' === $preset ? 'button-primary' : ''; ?>">Hôm nay</a>
                            <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'logs', 'preset' => 'yesterday' ], admin_url( 'admin.php' ) ) ); ?>" 
                               class="button <?php echo 'yesterday' === $preset ? 'button-primary' : ''; ?>">Hôm qua</a>
                            <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'logs', 'preset' => '7' ], admin_url( 'admin.php' ) ) ); ?>" 
                               class="button <?php echo '7' === $preset ? 'button-primary' : ''; ?>">7 ngày qua</a>
                            <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'logs', 'preset' => '30' ], admin_url( 'admin.php' ) ) ); ?>" 
                               class="button <?php echo '30' === $preset ? 'button-primary' : ''; ?>">30 ngày qua</a>
                        </div>
                        
                        <div class="filter-custom">
                            <span>Từ:</span>
                            <input type="date" name="start_date" value="<?php echo esc_attr( $start_date ); ?>" />
                            <span>Đến:</span>
                            <input type="date" name="end_date" value="<?php echo esc_attr( $end_date ); ?>" />
                            <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Tìm từ khóa / IP..." />
                            <button type="submit" class="button button-secondary">Lọc dữ liệu</button>
                        </div>
                    </div>
                </form>

                <!-- Bảng Dữ Liệu Logs -->
                <table class="widefat striped table-logs">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 140px;">Thời gian</th>
                            <th style="width: 140px;">Mẫu phôi</th>
                            <th>Nội dung câu chữ meme</th>
                            <th style="width: 130px;">Địa chỉ IP</th>
                            <th style="width: 120px;">Tài khoản</th>
                            <th style="width: 90px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $logs ) ) : ?>
                            <?php foreach ( $logs as $log ) : ?>
                                <tr>
                                    <td><strong>#<?php echo esc_html( $log->id ); ?></strong></td>
                                    <td><?php echo esc_html( date_i18n( 'd/m/Y H:i', strtotime( $log->created_at ) ) ); ?></td>
                                    <td><code><?php echo esc_html( $log->template ); ?></code></td>
                                    <td><strong><?php echo esc_html( $log->meme_text ?: '—' ); ?></strong></td>
                                    <td><code><?php echo esc_html( $log->ip_address ); ?></code></td>
                                    <td>
                                        <?php if ( $log->user_id > 0 ) : 
                                            $u = get_user_by( 'id', $log->user_id );
                                        ?>
                                            <span class="badge-user">👤 <?php echo esc_html( $u ? $u->display_name : 'ID #' . $log->user_id ); ?></span>
                                        <?php else : ?>
                                            <span class="badge-guest">Khách vãng lai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-success">Thành công</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 24px; color: #888;">
                                    Chưa có bản ghi nhật ký nào trong khoảng thời gian này.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Phân trang -->
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo number_format_i18n( $total_logs ); ?> bản ghi</span>
                            <?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
                                <a href="<?php echo esc_url( add_query_arg( [ 'page' => 'nguu-lai-settings', 'tab' => 'logs', 'paged' => $p, 'start_date' => $start_date, 'end_date' => $end_date, 's' => $search ], admin_url( 'admin.php' ) ) ); ?>" 
                                   class="page-numbers <?php echo $paged === $p ? 'current' : ''; ?>">
                                    <?php echo esc_html( $p ); ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <!-- ===================================================================
             TAB 5: BẢO MẬT & CHẶN IP
             =================================================================== -->
        <?php elseif ( 'security' === $current_tab ) : ?>
            
            <!-- Form Thêm IP Chặn Thủ Công -->
            <div class="nguu-lai-card">
                <div class="card-header">
                    <h2>Khóa Địa Chỉ IP Thủ Công</h2>
                    <p>Ngăn chặn ngay lập tức các địa chỉ IP có hành vi lạm dụng hoặc spam yêu cầu.</p>
                </div>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="form-inline-block">
                    <input type="hidden" name="action" value="nguu_lai_block_ip" />
                    <?php wp_nonce_field( 'nguu_lai_block_ip_action', 'nguu_lai_nonce' ); ?>
                    
                    <input type="text" name="block_ip" placeholder="Nhập địa chỉ IPv4 hoặc IPv6..." required class="regular-text" />
                    <input type="text" name="block_reason" placeholder="Lý do khóa (tùy chọn)..." class="regular-text" />
                    <button type="submit" class="button button-primary">🚫 Chặn IP Này</button>
                </form>
            </div>

            <!-- Bảng Danh Sách IP Bị Chặn -->
            <div class="nguu-lai-card">
                <div class="card-header-flex">
                    <h2>Danh Sách IP Đang Bị Chặn (<?php echo count( $blocked_ips ); ?>)</h2>
                    <?php if ( ! empty( $blocked_ips ) ) : ?>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Bạn có chắc muốn mở khóa toàn bộ IP không?');">
                            <input type="hidden" name="action" value="nguu_lai_clear_blocklist" />
                            <?php wp_nonce_field( 'nguu_lai_clear_blocklist_action', 'nguu_lai_nonce' ); ?>
                            <button type="submit" class="button button-link-delete">Mở khóa tất cả</button>
                        </form>
                    <?php endif; ?>
                </div>

                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Địa chỉ IP</th>
                            <th>Lý do</th>
                            <th style="width: 180px;">Thời gian chặn</th>
                            <th style="width: 100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $blocked_ips ) ) : ?>
                            <?php foreach ( $blocked_ips as $b_item ) : ?>
                                <tr>
                                    <td><code><?php echo esc_html( $b_item['ip'] ?? '' ); ?></code></td>
                                    <td><?php echo esc_html( $b_item['reason'] ?? 'Chặn thủ công' ); ?></td>
                                    <td><?php echo esc_html( $b_item['created_at'] ?? '—' ); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url( wp_nonce_url( add_query_arg( [ 'action' => 'nguu_lai_unblock_ip', 'ip' => $b_item['ip'] ], admin_url( 'admin-post.php' ) ), 'nguu_lai_unblock_ip_action', 'nguu_lai_nonce' ) ); ?>" 
                                           class="button button-small" onclick="return confirm('Mở chặn IP này?');">
                                           🔓 Mở chặn
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px; color: #888;">
                                    Hiện tại chưa có địa chỉ IP nào trong danh sách chặn.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </div>
</div>
