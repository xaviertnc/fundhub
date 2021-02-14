<?php

class FH_Reset_WP {


  function __construct()
  {
    add_action( 'admin_post_fh_wp_reset', array( $this, 'reset' ) );
  }


  function reset()
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

    $do_clear_content   = ! empty( $_POST[ 'clear_content' ] );
    $do_restore_plugins = ! empty( $_POST[ 'restore_plugins' ] );
    $do_restore_theme   = ! empty( $_POST[ 'restore_theme' ] );
    $do_delete_uploads  = ! empty( $_POST[ 'delete_uploads' ] );
    $confirmed_delete   = ! empty( $_POST[ 'confirm_delete_uploads' ] );

    // save values that need to be restored after reset
    //$show_on_front = get_option( 'show_on_front' );
    //$page_on_front = get_option( 'page_on_front' );
    //$page_for_posts = get_option( 'page_for_posts' );
    $blogname = get_option( 'blogname' );
    $blog_description = get_option( 'blogdescription' );
    $blog_public = get_option( 'blog_public' ); // Discourage search engines (1 or 0)
    $wplang = get_option( 'WPLANG', 'en_ZA' );
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
      $do_restore_theme ? $blogname : 'Vanilla Wordpress',
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

    // Delete all posts, post metas, comments & comment metas
    if ( $do_clear_content )
    {
      $tables = array( $wpdb->postmeta, $wpdb->posts,
        $wpdb->comments, $wpdb->commentmeta );
      foreach ( $tables as $table )
      {
        $wpdb->query( "TRUNCATE TABLE $table" );
      }
    }

    if ( $do_delete_uploads and $confirmed_delete )
    {
      $uploads_info = wp_get_upload_dir();
      $base_dir = uploads_info[ 'basedir' ];
      FH_Lib::$delete_count = 0;
      FH_Lib::delete_dir( $base_dir, $base_dir );
    }

    if ( $do_restore_plugins )
    {
      foreach ( $active_plugins as $plugin_file )
      {
        activate_plugin( $plugin_file );
      }
    }

    if ( $do_restore_theme )
    {
      switch_theme( $active_theme->get_stylesheet() );
      // set theme default options
      //update_option( 'show_on_front', $show_on_front, true );
      //update_option( 'page_for_posts', $page_for_posts, true );
      //update_option( 'page_on_front', $page_on_front, true );
      update_option( 'blogdescription', $blog_description, true );
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

}


new FH_Reset_WP();