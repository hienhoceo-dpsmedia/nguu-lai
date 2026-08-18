/**
 * Javascript Bổ trợ Giao diện Quản trị Ngưu Lai.
 *
 * @package NguuLai
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Sao chép mã Shortcode vào clipboard
        $('#btn-copy-shortcode').on('click', function () {
            const input = document.getElementById('nguu-lai-shortcode-input');
            if (input) {
                input.select();
                input.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(input.value).then(function () {
                    const btn = $('#btn-copy-shortcode');
                    const origText = btn.text();
                    btn.text('Đã sao chép! ✅').prop('disabled', true);
                    setTimeout(function () {
                        btn.text(origText).prop('disabled', false);
                    }, 2000);
                });
            }
        });
    });
})(jQuery);
