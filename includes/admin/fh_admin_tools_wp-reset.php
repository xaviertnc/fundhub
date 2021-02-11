<?php

function fh_action_wp_reset()
{
	global $current_user, $wpdb;

  check_admin_referer( 'fh_nonce' );

  if ( ! current_user_can( 'administrator' ) )
  {
    return false;
  }

  // make sure the function is available to us
  if ( ! function_exists( 'wp_install' ) )
  {
    require ABSPATH . '/wp-admin/includes/upgrade.php';
  }

  // save values that need to be restored after reset
  $blogname = get_option( 'blogname' );
  $blog_public = get_option( 'blog_public' ); // Discourage search engines (1 or 0)
  $siteurl = get_option( 'siteurl' );
  $home = get_option( 'home' );

  $active_plugins = get_option( 'active_plugins' );
  $active_theme = wp_get_theme();

  // delete custom tables with WP's prefix
  $prefix = str_replace( '_', '\_', $wpdb->prefix );
  $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$prefix}%'" );
  foreach ( $tables as $table )
  {
    $wpdb->query( "DROP TABLE $table" );
  }

  $result = @wp_install(
    $blogname,
    $current_user->user_login,
  	$current_user->user_email,
    $blog_public,
    '',
    md5(rand()),
    $wplang
  );
  $user_id = $result[ 'user_id' ];

  // restore user pass
  $sql = "UPDATE {$wpdb->users} SET user_pass = %s, user_activation_key = ''" .
   ' WHERE ID = %d LIMIT 1';

  $query = $wpdb->prepare( $sql, array( $current_user->user_pass, $user_id ) );
  $wpdb->query( $query );

  // restore rest of the settings
  update_option( 'siteurl', $siteurl, true );
  update_option( 'home', $home, true );

  // remove password nag
  if ( get_user_meta( $user_id, 'default_password_nag' ) )
  {
    update_user_meta( $user_id, 'default_password_nag', false );
  }
  if ( get_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' ) )
  {
    update_user_meta( $user_id, $wpdb->prefix . 'default_password_nag', false );
  }

  // delete all default pages, posts & comments
  if ( ! empty( $_POST[ 'clear_content' ] ) )
  {
    $tables = array( $wpdb->postmeta, $wpdb->posts, $wpdb->comments );
    foreach ( $tables as $table )
    {
      $wpdb->query( "TRUNCATE TABLE $table" );
    }
  }

  // reactivate all plugins
  if ( ! empty( $_POST[ 'keep_plugins' ] ) )
  {
    foreach ( $active_plugins as $plugin_file )
    {
      activate_plugin( $plugin_file );
    }
  }

  // reactivate theme
  if ( ! empty( $_POST[ 'keep_theme' ] ) )
  {
    switch_theme( $active_theme->get_stylesheet() );
    // set theme default options
    update_option( 'permalink_structure', '/%category%/%postname%/', true );
    update_option( 'uploads_use_yearmonth_folders', 0, true );
    update_option( 'wp_page_for_privacy_policy', 0, true );
    update_option( 'upload_path', 'media', true );
    update_option( 'thumbnail_crop', 0, true );
    update_option( 'ping_sites', '', true );
  }

  // log out and log in
  wp_clear_auth_cookie();
  wp_set_auth_cookie( $user_id );

  wp_redirect( admin_url() . '?wp-reset=success' );

  exit;

}

add_action( 'admin_post_fh_wp_reset', 'fh_action_wp_reset' );