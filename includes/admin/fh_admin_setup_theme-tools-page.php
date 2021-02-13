<?php /* FUND HUB Theme Options Admin Page */


function fh_render_tools_page()
{
  $nonce_field = wp_nonce_field( 'fh_nonce', '_wpnonce', true, false );
  include THEME_DIR . '/templates/template-tools-page.php';
}


function fh_create_tools_submenu()
{
  add_management_page( 'Theme Tools', 'HUB Tools', 'manage_options',
    'fh_tools', 'fh_render_tools_page', 0 );
}

add_action( 'admin_menu', 'fh_create_tools_submenu' );