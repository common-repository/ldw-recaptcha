<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

$option_name = 'ldw_recaptcha_settings';
delete_option( $option_name );
?>
