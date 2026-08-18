/**
 * Trình Tạo Meme Ngưu Lai — Canvas 2D Workbench Engine.
 * Hỗ trợ chọn 10 Google Fonts chuẩn 100% Tiếng Việt.
 *
 * @package NguuLai
 */

(function () {
    'use strict';

    window.NguuLaiWorkbench = {
        canvas: null,
        ctx: null,
        textInput: null,
        fontSelect: null,
        sizeSlider: null,
        xSlider: null,
        ySlider: null,
        watermarkCheckbox: null,
        uploadInput: null,
        sizeOutput: null,
        xOutput: null,
        yOutput: null,
        templateGrid: null,
        phraseGrid: null,
        activeImage: new Image(),
        activeTemplateIndex: 0,
        customUploadUrl: '',
        sessionId: '',
        toastTimer: null,

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

            this.canvas = document.getElementById('meme-canvas');
            if (!this.canvas) return;

            this.ctx = this.canvas.getContext('2d');
            this.textInput = document.getElementById('meme-text');
            this.fontSelect = document.getElementById('meme-font');
            this.sizeSlider = document.getElementById('meme-size');
            this.xSlider = document.getElementById('meme-x');
            this.ySlider = document.getElementById('meme-y');
            this.watermarkCheckbox = document.getElementById('meme-watermark');
            this.uploadInput = document.getElementById('meme-upload');
            this.sizeOutput = document.getElementById('meme-size-output');
            this.xOutput = document.getElementById('meme-x-output');
            this.yOutput = document.getElementById('meme-y-output');
            this.templateGrid = document.getElementById('meme-template-grid');
            this.phraseGrid = document.getElementById('phrase-grid');

            this.sessionId = this.generateUUID();
            this.activeTemplateIndex = data.initial_template !== undefined ? data.initial_template : 0;

            if (data.initial_text && this.textInput && !this.textInput.value) {
                this.textInput.value = data.initial_text;
            }

            if (data.initial_font && this.fontSelect) {
                this.fontSelect.value = data.initial_font;
            }

            this.bindTemplateGridEvents();
            this.bindPhraseGridEvents();
            this.bindEvents();
            this.applyFontToInput();
            this.updateQuotaDisplay();


            // Tìm URL của template ban đầu
            let initialTemplateUrl = '';
            if (this.templateGrid) {
                const activeBtn = this.templateGrid.querySelector('button.is-active') || this.templateGrid.querySelector('button');
                if (activeBtn && activeBtn.dataset.src) {
                    initialTemplateUrl = activeBtn.dataset.src;
                }
            }

            if (!initialTemplateUrl && data.templates && data.templates[this.activeTemplateIndex]) {
                initialTemplateUrl = data.templates[this.activeTemplateIndex];
            }

            if (initialTemplateUrl) {
                this.setImage(initialTemplateUrl, this.activeTemplateIndex);
            } else {
                this.drawMeme();
            }

            // Nạp font chữ và vẽ lại khi Google Fonts sẵn sàng
            if (document.fonts && document.fonts.ready) {
                const self = this;
                document.fonts.ready.then(function () {
                    self.drawMeme();
                });
            }
        },

        generateUUID: function () {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = (Math.random() * 16) | 0;
                const v = c === 'x' ? r : (r & 0x3) | 0x8;
                return v.toString(16);
            });
        },

        showToast: function (message) {
            const toast = document.getElementById('nguu-lai-toast');
            if (!toast) return;

            toast.textContent = message;
            toast.classList.add('is-visible');

            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(function () {
                toast.classList.remove('is-visible');
            }, 3200);
        },

        updateQuotaDisplay: function () {
            const data = window.nguuLaiData || {};
            const display = document.getElementById('quota-text-display');
            if (!display) return;

            if (data.is_logged_in) {
                display.textContent = 'Lượt tải: Không giới hạn ✨';
                return;
            }

            const isRequireLogin = data.quota && data.quota.require_login;
            if (isRequireLogin) {
                display.textContent = 'Bắt buộc đăng nhập Google để tải meme';
                return;
            }

            const remaining = data.quota && data.quota.remaining_quota !== undefined ? data.quota.remaining_quota : 5;
            const limit = data.quota && data.quota.daily_limit !== undefined ? data.quota.daily_limit : 5;

            if (remaining > 0) {
                display.textContent = 'Lượt tải miễn phí hôm nay: ' + remaining + '/' + limit + ' lượt';
            } else {
                display.textContent = 'Đã hết lượt tải miễn phí (0/' + limit + '). Đăng nhập Google để tải thoải mái!';
            }
        },

        wrapText: function (ctx, text, maxWidth) {
            const words = text.split(' ');
            const lines = [];
            let currentLine = '';

            for (let i = 0; i < words.length; i++) {
                const word = words[i];
                const testLine = currentLine ? currentLine + ' ' + word : word;
                const metrics = ctx.measureText(testLine);

                if (metrics.width > maxWidth && currentLine) {
                    lines.push(currentLine);
                    currentLine = word;
                } else {
                    currentLine = testLine;
                }
            }

            if (currentLine) {
                lines.push(currentLine);
            }

            return lines.slice(0, 4);
        },

        getFontConfig: function () {
            const data = window.nguuLaiData || {};
            const fontName = this.fontSelect ? this.fontSelect.value : (data.initial_font || 'Montserrat');
            
            let weight = '900';
            let family = '"' + fontName + '", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';

            if (data.fonts && Array.isArray(data.fonts)) {
                const found = data.fonts.find(function (f) {
                    return f.name === fontName;
                });
                if (found) {
                    weight = found.weight || '900';
                    family = (found.family || ('"' + fontName + '"')) + ', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
                }
            }

            return {
                name: fontName,
                weight: weight,
                family: family,
            };
        },

        applyFontToInput: function () {
            if (this.textInput) {
                const fontCfg = this.getFontConfig();
                this.textInput.style.fontFamily = fontCfg.family;
                this.textInput.style.fontWeight = fontCfg.weight;
            }
        },

        drawMeme: function () {

            const ctx = this.ctx;
            const canvas = this.canvas;
            if (!ctx || !canvas) return;

            const data = window.nguuLaiData || {};

            // Nền màu tối
            ctx.fillStyle = '#191b18';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Vẽ hình ảnh phôi meme (Scale cover)
            if (this.activeImage && (this.activeImage.complete || this.activeImage.naturalWidth)) {
                const img = this.activeImage;
                if (img.naturalWidth > 0 && img.naturalHeight > 0) {
                    const scale = Math.max(canvas.width / img.naturalWidth, canvas.height / img.naturalHeight);
                    const width = img.naturalWidth * scale;
                    const height = img.naturalHeight * scale;
                    const x = (canvas.width - width) / 2;
                    const y = (canvas.height - height) / 2;

                    ctx.drawImage(img, x, y, width, height);
                }
            }

            // Vẽ Chữ Meme
            const text = (this.textInput ? this.textInput.value : '').trim();
            const fontSize = Number(this.sizeSlider ? this.sizeSlider.value : 64);
            const posX = Number(this.xSlider ? this.xSlider.value : 50);
            const posY = Number(this.ySlider ? this.ySlider.value : 82);
            const fontCfg = this.getFontConfig();

            if (text) {
                ctx.font = fontCfg.weight + ' ' + fontSize + 'px ' + fontCfg.family;
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.lineJoin = 'round';
                ctx.miterLimit = 2;

                // Viền chữ màu đen dày tạo độ tương phản cao
                ctx.strokeStyle = '#111111';
                ctx.lineWidth = Math.max(6, fontSize * 0.14);

                // Ruột chữ màu trắng
                ctx.fillStyle = '#ffffff';

                const maxWidth = 820;
                const lines = this.wrapText(ctx, text, maxWidth);
                const lineHeight = fontSize * 1.18;
                const centerY = canvas.height * (posY / 100);
                const startY = centerY - ((lines.length - 1) * lineHeight) / 2;
                const targetX = canvas.width * (posX / 100);

                lines.forEach(function (line, index) {
                    const lineY = startY + index * lineHeight;
                    ctx.strokeText(line, targetX, lineY, maxWidth);
                    ctx.fillText(line, targetX, lineY, maxWidth);
                });
            }

            // Vẽ Watermark
            const watermarkChecked = this.watermarkCheckbox ? this.watermarkCheckbox.checked : true;
            if (watermarkChecked) {
                const watermarkText = data.watermark_text || 'niulai.wiki';
                ctx.font = '700 22px -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif';
                ctx.textAlign = 'right';
                ctx.textBaseline = 'alphabetic';

                ctx.strokeStyle = 'rgba(0, 0, 0, 0.6)';
                ctx.lineWidth = 3;
                ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';

                ctx.strokeText(watermarkText, 874, 874);
                ctx.fillText(watermarkText, 874, 874);
            }
        },

        setImage: function (src, templateIndex) {
            const self = this;
            const data = window.nguuLaiData || {};

            this.activeTemplateIndex = templateIndex;
            
            const newImg = new Image();
            newImg.crossOrigin = 'anonymous';
            newImg.onload = function () {
                self.activeImage = newImg;
                self.drawMeme();
            };
            newImg.onerror = function () {
                self.showToast(data.i18n && data.i18n.upload_error ? data.i18n.upload_error : 'Không thể tải hình ảnh.');
            };
            newImg.src = src;

            if (newImg.complete && newImg.naturalWidth > 0) {
                self.activeImage = newImg;
                self.drawMeme();
            }

            // Cập nhật trạng thái active trên lưới template
            if (this.templateGrid) {
                const buttons = this.templateGrid.querySelectorAll('button');
                buttons.forEach(function (button, idx) {
                    const btnIndex = button.dataset.index !== undefined ? parseInt(button.dataset.index, 10) : idx;
                    const isActive = btnIndex === templateIndex;
                    button.classList.toggle('is-active', isActive);
                    button.setAttribute('aria-pressed', String(isActive));
                    const span = button.querySelector('span');
                    if (span) {
                        span.textContent = isActive ? (data.i18n && data.i18n.current ? data.i18n.current : 'Đang chọn') : (data.i18n && data.i18n.use ? data.i18n.use : 'Dùng mẫu này');
                    }
                });
            }
        },

        bindTemplateGridEvents: function () {
            const self = this;
            if (!this.templateGrid) return;

            const buttons = this.templateGrid.querySelectorAll('button');
            buttons.forEach(function (button, index) {
                button.addEventListener('click', function () {
                    const src = button.dataset.src;
                    const idx = button.dataset.index !== undefined ? parseInt(button.dataset.index, 10) : index;
                    if (src) {
                        self.setImage(src, idx);
                    }
                });
            });
        },

        bindPhraseGridEvents: function () {
            const self = this;
            const data = window.nguuLaiData || {};
            if (!this.phraseGrid) return;

            const buttons = this.phraseGrid.querySelectorAll('button');
            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const phrase = button.dataset.phrase || button.textContent.trim();
                    if (self.textInput && phrase) {
                        self.textInput.value = phrase;
                        self.drawMeme();
                        self.showToast(data.i18n && data.i18n.phrase_selected ? data.i18n.phrase_selected : 'Đã áp dụng câu thoại!');
                    }
                });
            });
        },

        bindEvents: function () {
            const self = this;
            const data = window.nguuLaiData || {};

            // Nhập text
            if (this.textInput) {
                this.textInput.addEventListener('input', function () {
                    self.drawMeme();
                });
            }

            // Đổi Phông Chữ Google
            if (this.fontSelect) {
                this.applyFontToInput();
                this.fontSelect.addEventListener('change', function () {
                    const fontCfg = self.getFontConfig();
                    self.applyFontToInput();
                    if (document.fonts && document.fonts.load) {
                        document.fonts.load(fontCfg.weight + ' 48px "' + fontCfg.name + '"').then(function () {
                            self.drawMeme();
                        });
                    }
                    self.drawMeme();
                    self.showToast('Đã đổi phông chữ: ' + fontCfg.name);
                });
            }


            // Thanh trượt cỡ chữ
            if (this.sizeSlider) {
                this.sizeSlider.addEventListener('input', function () {
                    if (self.sizeOutput) self.sizeOutput.textContent = self.sizeSlider.value + 'px';
                    self.drawMeme();
                });
            }

            // Thanh trượt X
            if (this.xSlider) {
                this.xSlider.addEventListener('input', function () {
                    if (self.xOutput) self.xOutput.textContent = self.xSlider.value + '%';
                    self.drawMeme();
                });
            }

            // Thanh trượt Y
            if (this.ySlider) {
                this.ySlider.addEventListener('input', function () {
                    if (self.yOutput) self.yOutput.textContent = self.ySlider.value + '%';
                    self.drawMeme();
                });
            }

            // Checkbox Watermark
            if (this.watermarkCheckbox) {
                this.watermarkCheckbox.addEventListener('change', function () {
                    self.drawMeme();
                });
            }

            // Tải ảnh tùy chỉnh từ máy tính/điện thoại
            if (this.uploadInput) {
                this.uploadInput.addEventListener('change', function () {
                    const file = self.uploadInput.files && self.uploadInput.files[0];
                    if (!file || !file.type.startsWith('image/')) {
                        self.showToast(data.i18n && data.i18n.upload_error ? data.i18n.upload_error : 'Định dạng tệp không hợp lệ.');
                        return;
                    }

                    if (self.customUploadUrl) {
                        URL.revokeObjectURL(self.customUploadUrl);
                    }

                    self.customUploadUrl = URL.createObjectURL(file);
                    self.setImage(self.customUploadUrl, -1);
                    self.showToast(data.i18n && data.i18n.image_loaded ? data.i18n.image_loaded : 'Đã chọn ảnh của bạn thành công!');
                });
            }

            // Đổi câu ngẫu nhiên
            const randomBtn = document.getElementById('meme-random');
            if (randomBtn) {
                randomBtn.addEventListener('click', function () {
                    let phraseList = (data.phrases && data.phrases.length) ? data.phrases : [];
                    if (!phraseList.length && self.phraseGrid) {
                        const btns = self.phraseGrid.querySelectorAll('button');
                        phraseList = Array.from(btns).map(function (b) {
                            return b.dataset.phrase || b.textContent.trim();
                        });
                    }

                    if (phraseList.length) {
                        const randomIndex = Math.floor(Math.random() * phraseList.length);
                        if (self.textInput) {
                            self.textInput.value = phraseList[randomIndex];
                            self.drawMeme();
                            self.showToast('Đã đổi: "' + phraseList[randomIndex] + '"');
                        }
                    }
                });
            }

            // Tải Meme về máy
            const downloadBtn = document.getElementById('meme-download');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function () {
                    self.handleDownload();
                });
            }
        },

        handleDownload: function () {
            const self = this;
            const data = window.nguuLaiData || {};

            // Kiểm tra hạn mức
            if (!data.is_logged_in) {
                const remaining = data.quota && data.quota.remaining_quota !== undefined ? data.quota.remaining_quota : 5;
                const requireLogin = (data.quota && data.quota.require_login) || false;

                if (requireLogin || remaining <= 0) {
                    if (window.NguuLaiAuth && window.NguuLaiAuth.openLoginModal) {
                        window.NguuLaiAuth.openLoginModal(data.i18n && data.i18n.quota_exceeded ? data.i18n.quota_exceeded : 'Hết lượt tải miễn phí');
                    }
                    return;
                }
            }

            this.drawMeme();

            this.canvas.toBlob(function (blob) {
                if (!blob) return;

                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                const cleanText = (self.textInput ? self.textInput.value : 'nguu-lai').trim().toLowerCase().replace(/[^a-z0-9\u00C0-\u1EF9]/gi, '-');
                a.href = url;
                a.download = 'meme-nguu-lai-' + (cleanText || 'custom') + '.png';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                setTimeout(function () {
                    URL.revokeObjectURL(url);
                }, 1500);

                self.showToast(data.i18n && data.i18n.download_ready ? data.i18n.download_ready : 'Đã tải meme thành công! 🎉');

                self.sendLogUpdate();
            }, 'image/png');
        },

        sendLogUpdate: function () {
            const self = this;
            const data = window.nguuLaiData || {};
            if (!data.rest_url) return;

            let templateName = 'custom_upload';
            if (self.activeTemplateIndex >= 0 && data.templates && data.templates[self.activeTemplateIndex]) {
                templateName = data.templates[self.activeTemplateIndex].split('/').pop();
            }

            fetch(data.rest_url + '/update-log', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': data.rest_nonce || '',
                },
                body: JSON.stringify({
                    template: templateName,
                    text: self.textInput ? self.textInput.value : '',
                    session_id: self.sessionId,
                }),
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (result) {
                    if (result.success && result.data) {
                        if (!data.is_logged_in && data.quota) {
                            data.quota.remaining_quota = result.data.remaining_quota;
                            self.updateQuotaDisplay();
                        }
                    }
                })
                .catch(function () {});
        },
    };

    // Tự động khởi tạo khi DOM ready hoặc nếu đã ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            window.NguuLaiWorkbench.init();
        });
    } else {
        window.NguuLaiWorkbench.init();
    }
})();
