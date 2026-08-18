<?php
namespace NguuLai\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Quản lý ngôn ngữ và đa ngữ hóa (i18n).
 */
class I18n {

    public function load_plugin_textdomain(): void {
        load_plugin_textdomain(
            'nguu-lai',
            false,
            dirname( NGUU_LAI_PLUGIN_BASENAME ) . '/languages/'
        );
    }
}
