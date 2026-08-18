/**
 * Xử lý Đăng nhập Google One-Tap & Google Identity Services Button.
 *
 * @package NguuLai
 */

(function () {
    'use strict';

    window.NguuLaiAuth = {
        gsiInitialized: false,
        modalBtnRendered: false,

        init: function () {
            // Đọc dữ liệu từ window.nguuLaiData hoặc fallback từ JSON script nhúng trực tiếp
            let data = window.nguuLaiData;
            if (!data) {
                const configScript = document.getElementById('nguu-lai-config-json');
                if (configScript && configScript.textContent) {
                    try {
                        data = JSON.parse(configScript.textContent);
                        window.nguuLaiData = data;
                    } catch (e) {
                        data = {};
                    }
                }
            }
            data = data || {};

            if (!data.google_client_id) {
                return;
            }

            // Nếu đã đăng nhập thì không cần khởi tạo Google Login
            if (data.is_logged_in) {
                return;
            }

            this.setupGSI();
        },

        setupGSI: function () {
            const self = this;

            const checkGSILoaded = setInterval(function () {
                if (window.google && window.google.accounts && window.google.accounts.id) {
                    clearInterval(checkGSILoaded);
                    self.renderGoogleButtons();
                }
            }, 100);

            // Tự hủy kiểm tra sau 10s nếu GSI không tải được
            setTimeout(function () {
                clearInterval(checkGSILoaded);
            }, 10000);
        },

        renderGoogleButtons: function () {
            const self = this;
            const data = window.nguuLaiData || {};
            if (!data.google_client_id || !window.google || !window.google.accounts || !window.google.accounts.id) {
                return;
            }

            if (!this.gsiInitialized) {
                google.accounts.id.initialize({
                    client_id: data.google_client_id,
                    callback: function (response) {
                        self.handleCredentialResponse(response);
                    },
                    auto_select: false,
                    cancel_on_tap_outside: true,
                });
                this.gsiInitialized = true;
            }

            // 1. Nút đăng nhập trên thanh auth bar
            const authBarSlot = document.getElementById('google-signin-btn-container');
            if (authBarSlot) {
                authBarSlot.innerHTML = '';
                google.accounts.id.renderButton(authBarSlot, {
                    theme: 'outline',
                    size: 'medium',
                    text: 'signin_with',
                    shape: 'rectangular',
                    locale: 'vi',
                });
                const triggerBtn = document.getElementById('btn-trigger-google-login');
                if (triggerBtn) {
                    triggerBtn.style.display = 'none'; // Ẩn nút fallback khi nút Google chính thức đã render
                }
            }

            // 2. Nút đăng nhập trong Modal khi hết lượt
            const modalSlot = document.getElementById('modal-google-btn-slot');
            if (modalSlot) {
                modalSlot.innerHTML = '';
                google.accounts.id.renderButton(modalSlot, {
                    theme: 'filled_blue',
                    size: 'large',
                    text: 'continue_with',
                    shape: 'pill',
                    locale: 'vi',
                    width: 280,
                });
                this.modalBtnRendered = true;
            }

            // 3. Hiển thị One-Tap prompt nếu thích hợp
            try {
                google.accounts.id.prompt();
            } catch (e) {}
        },

        handleCredentialResponse: function (response) {
            const data = window.nguuLaiData || {};
            if (!response || !response.credential) {
                return;
            }

            if (window.NguuLaiWorkbench && window.NguuLaiWorkbench.showToast) {
                window.NguuLaiWorkbench.showToast(data.i18n && data.i18n.logging_in ? data.i18n.logging_in : 'Đang đăng nhập Google...');
            }

            fetch(data.rest_url + '/google-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': data.rest_nonce || '',
                },
                body: JSON.stringify({
                    credential: response.credential,
                }),
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (result) {
                    if (result.success && result.data) {
                        data.is_logged_in = true;
                        data.user = result.data;
                        data.quota = {
                            is_logged_in: true,
                            remaining_quota: -1,
                            daily_limit: -1,
                        };

                        // Cập nhật giao diện thanh auth bar
                        const authBar = document.getElementById('nguu-lai-auth-bar');
                        if (authBar) {
                            const leftSide = authBar.querySelector('.auth-bar-left');
                            const rightSide = authBar.querySelector('.auth-bar-right');

                            if (leftSide) {
                                leftSide.innerHTML =
                                    '<div class="user-pill is-vip">' +
                                    (result.data.avatar ? '<img src="' + result.data.avatar + '" alt="Avatar" class="user-avatar" />' : '') +
                                    '<span class="user-name">' + (result.data.name || 'Người dùng VIP') + '</span>' +
                                    '<span class="badge-unlimited">Lượt tải: Không giới hạn ✨</span>' +
                                    '</div>';
                            }

                            if (rightSide) {
                                rightSide.innerHTML = '';
                            }
                        }

                        // Đóng modal nếu đang mở
                        const modal = document.getElementById('google-login-modal');
                        if (modal) {
                            modal.hidden = true;
                        }

                        if (window.NguuLaiWorkbench && window.NguuLaiWorkbench.showToast) {
                            window.NguuLaiWorkbench.showToast(data.i18n && data.i18n.login_success ? data.i18n.login_success : 'Đăng nhập thành công! Lượt tải không giới hạn ✨');
                        }
                    } else {
                        if (window.NguuLaiWorkbench && window.NguuLaiWorkbench.showToast) {
                            window.NguuLaiWorkbench.showToast(result.message || (data.i18n && data.i18n.login_failed ? data.i18n.login_failed : 'Đăng nhập thất bại.'));
                        }
                    }
                })
                .catch(function () {
                    if (window.NguuLaiWorkbench && window.NguuLaiWorkbench.showToast) {
                        window.NguuLaiWorkbench.showToast(data.i18n && data.i18n.login_failed ? data.i18n.login_failed : 'Lỗi kết nối máy chủ.');
                    }
                });
        },

        openLoginModal: function (customMessage) {
            const data = window.nguuLaiData || {};
            const modal = document.getElementById('google-login-modal');
            if (!modal) return;

            if (!data.google_client_id) {
                if (window.NguuLaiWorkbench && window.NguuLaiWorkbench.showToast) {
                    window.NguuLaiWorkbench.showToast('Chưa cấu hình Google Client ID trong trang quản trị.');
                }
                return;
            }

            if (customMessage) {
                const desc = document.getElementById('modal-desc-text');
                if (desc) desc.textContent = customMessage;
            }

            // Hiện modal
            modal.hidden = false;

            // Đảm bảo nút Google được render bên trong modal khi modal hiển thị
            const modalSlot = document.getElementById('modal-google-btn-slot');
            if (modalSlot && window.google && window.google.accounts && window.google.accounts.id) {
                if (!this.gsiInitialized) {
                    this.renderGoogleButtons();
                } else if (!modalSlot.hasChildNodes()) {
                    google.accounts.id.renderButton(modalSlot, {
                        theme: 'filled_blue',
                        size: 'large',
                        text: 'continue_with',
                        shape: 'pill',
                        locale: 'vi',
                        width: 280,
                    });
                }
            }
        },
    };

    function initAll() {
        window.NguuLaiAuth.init();

        const triggerBtn = document.getElementById('btn-trigger-google-login');
        if (triggerBtn) {
            triggerBtn.addEventListener('click', function () {
                window.NguuLaiAuth.openLoginModal();
            });
        }

        const closeBtn = document.getElementById('modal-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                const modal = document.getElementById('google-login-modal');
                if (modal) modal.hidden = true;
            });
        }

        const modalBackdrop = document.querySelector('#google-login-modal .modal-backdrop');
        if (modalBackdrop) {
            modalBackdrop.addEventListener('click', function () {
                const modal = document.getElementById('google-login-modal');
                if (modal) modal.hidden = true;
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
