/**
 * Xử lý Đăng nhập Google One-Tap & Google Identity Services Button.
 *
 * @package NguuLai
 */

(function () {
    'use strict';

    window.NguuLaiAuth = {
        gsiInitialized: false,

        init: function () {
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

            if (data.is_logged_in) {
                return;
            }

            this.setupGSI();
        },

        setupGSI: function (onReadyCallback) {
            const self = this;

            function isGSIAvailable() {
                return window.google && window.google.accounts && window.google.accounts.id;
            }

            function onReady() {
                self.initGSIInstance();
                self.renderAuthBarButton();
                if (typeof onReadyCallback === 'function') {
                    onReadyCallback();
                }
            }

            if (isGSIAvailable()) {
                onReady();
                return;
            }

            // Đảm bảo script GSI được tải với đầy đủ thuộc tính chống cache/defer
            if (!document.querySelector('script[src*="accounts.google.com/gsi/client"]')) {
                const script = document.createElement('script');
                script.src = 'https://accounts.google.com/gsi/client';
                script.async = true;
                script.defer = true;
                script.setAttribute('data-no-optimize', '1');
                script.setAttribute('data-cfasync', 'false');
                script.onload = function () {
                    if (isGSIAvailable()) {
                        onReady();
                    }
                };
                document.head.appendChild(script);
            }

            const checkGSILoaded = setInterval(function () {
                if (isGSIAvailable()) {
                    clearInterval(checkGSILoaded);
                    onReady();
                }
            }, 100);

            setTimeout(function () {
                clearInterval(checkGSILoaded);
            }, 10000);
        },

        initGSIInstance: function () {
            const self = this;
            const data = window.nguuLaiData || {};
            if (!data.google_client_id || !window.google || !window.google.accounts || !window.google.accounts.id) {
                return;
            }

            if (!this.gsiInitialized) {
                try {
                    google.accounts.id.initialize({
                        client_id: data.google_client_id,
                        callback: function (response) {
                            self.handleCredentialResponse(response);
                        },
                        auto_select: false,
                        cancel_on_tap_outside: true,
                    });
                    this.gsiInitialized = true;
                } catch (err) {
                    console.warn('[NguuLaiAuth] GSI initialization warning:', err);
                }
            }
        },

        renderAuthBarButton: function () {
            const data = window.nguuLaiData || {};
            if (!data.google_client_id || !window.google || !window.google.accounts || !window.google.accounts.id) {
                return;
            }

            this.initGSIInstance();

            const authBarSlot = document.getElementById('google-signin-btn-container');
            if (authBarSlot) {
                authBarSlot.innerHTML = '';
                try {
                    google.accounts.id.renderButton(authBarSlot, {
                        theme: 'outline',
                        size: 'medium',
                        text: 'signin_with',
                        shape: 'rectangular',
                        locale: 'vi',
                    });
                    const triggerBtn = document.getElementById('btn-trigger-google-login');
                    if (triggerBtn) {
                        triggerBtn.style.display = 'none';
                    }
                } catch (e) {
                    console.warn('[NguuLaiAuth] AuthBar renderButton failed:', e);
                }
            }

            try {
                google.accounts.id.prompt();
            } catch (e) {}
        },

        renderModalButton: function () {
            const data = window.nguuLaiData || {};
            const modalSlot = document.getElementById('modal-google-btn-slot');
            if (!modalSlot || !data.google_client_id) {
                return;
            }

            if (!window.google || !window.google.accounts || !window.google.accounts.id) {
                this.setupGSI(function () {
                    window.NguuLaiAuth.renderModalButton();
                });
                return;
            }

            this.initGSIInstance();

            modalSlot.innerHTML = '';
            try {
                google.accounts.id.renderButton(modalSlot, {
                    theme: 'filled_blue',
                    size: 'large',
                    text: 'continue_with',
                    shape: 'pill',
                    locale: 'vi',
                    width: 280,
                });
            } catch (e) {
                console.warn('[NguuLaiAuth] Modal renderButton failed:', e);
            }
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

            // 1. Mở hiển thị modal trước
            modal.hidden = false;

            // 2. Chờ layout hiển thị rồi mới render Google button trong modal
            const self = this;
            setTimeout(function () {
                self.renderModalButton();
            }, 60);
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

        const modalBackdrop = document.querySelector('#google-login-modal .nguu-lai-backdrop, #google-login-modal .modal-backdrop');
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
