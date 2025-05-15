<?php
// Verifica se o WordPress está chamando diretamente o arquivo
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}


// Remover uma opção salva no banco de dados
delete_option( 'wfb_phone_number' );
delete_option( 'wfb_text' );
delete_option( 'wfb_utm_params' );
delete_option( 'wfb_target_pages' );
delete_option( 'wfb_button_position' );

// Se o plugin salva opções em rede (multisite)
delete_site_option( 'wfb_phone_number' );
delete_site_option( 'wfb_text' );
delete_site_option( 'wfb_utm_params' );
delete_site_option( 'wfb_target_pages' );
delete_site_option( 'wfb_button_position' );
