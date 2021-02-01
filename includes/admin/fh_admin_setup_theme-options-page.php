<?php /* FUND HUB Theme Options Admin Page */


function fh_render_options_page()
{
  $nonce_field = wp_nonce_field( 'fh_nonce', '_wpnonce', true, false );
  include THEME_DIR . '/templates/template-theme-options.php';
}


function fh_add_options_page()
{
  add_options_page( 'Fund Hub Options', 'Fund Hub', 'manage_options',
    'fh_options', 'fh_render_options_page' );
}

add_action( 'admin_menu', 'fh_add_options_page' );