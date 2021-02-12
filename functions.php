<?php
/**
 * functions.php
 *
 * Theme: FUNDHUB
 * Author: Neels Moller
 * Version 1.0.0
 *
 * Created: November 2020
 * Last Update: January 2021
 *
 */

define( 'IS_ADMIN'  , is_admin() );

define( 'SITE_URL'  , site_url() );
define( 'SITE_NAME' , get_bloginfo( 'name' ) );
define( 'TAGLINE'   , get_bloginfo( 'description' ) );

define( 'THEME_DIR' , get_template_directory() );
define( 'THEME_URI' , get_template_directory_uri() );

define( 'FH_ASSETS', THEME_URI . '/assets' );
define( 'FH_INCLUDES', THEME_DIR . '/includes' );


include FH_INCLUDES . '/fh_setup_custom-post-types.php';
include FH_INCLUDES . '/fh_setup_disable-emojis.php';
include FH_INCLUDES . '/fh_setup_customizer.php';
include FH_INCLUDES . '/fh_setup_widgets.php';

include FH_INCLUDES . '/widgets/fh_widget_logos-grid.php';
include FH_INCLUDES . '/widgets/fh_widget_callout.php';
include FH_INCLUDES . '/widgets/fh_widget_image.php';


function fh_after_setup_theme()
{
  // Register Nav Menus
  register_nav_menus( array(
    'primary'   => 'Main/Header',
    'secondary' => 'Main/Footer'
  ) );

  // Add Theme Support Options
  add_theme_support( 'html5' );
  add_theme_support( 'align-wide' );
  add_theme_support( 'post-thumbnails' );
  add_theme_support( 'customize-selective-refresh-widgets' );
  add_theme_support( 'custom-logo', array( 'flex-width' => true ) );
}

add_action( 'after_setup_theme', 'fh_after_setup_theme', 10 );


if ( IS_ADMIN )
{
    include FH_INCLUDES . '/admin/fh_admin_setup_dashboard.php';
    include FH_INCLUDES . '/admin/fh_admin_setup_image-sizes.php';
    include FH_INCLUDES . '/admin/fh_admin_setup_default-content.php';
    include FH_INCLUDES . '/admin/fh_admin_setup_posts-list-page.php';
    include FH_INCLUDES . '/admin/fh_admin_setup_theme-tools-page.php';

    include FH_INCLUDES . '/admin/fh_admin_helper-functions.php';

    include FH_INCLUDES . '/admin/fh_admin_tools_export-data.php';
    include FH_INCLUDES . '/admin/fh_admin_tools_import-data.php';
    include FH_INCLUDES . '/admin/fh_admin_tools_wp-reset.php';

    include FH_INCLUDES . '/admin/fh_admin_ajax_media-page.php';

    // Enqueue backend assets ( Block Editor )
    function fh_enqueue_block_editor_assets()
    {
      wp_enqueue_style( 'fundhub_admin_editor',
        FH_ASSETS . '/css/fh_admin_editor.css',
        false
      );
      wp_enqueue_style( 'fundhub_admin_editor-blocks',
        FH_ASSETS . '/css/fh_admin_editor-blocks.css',
        false
      );
      wp_enqueue_script( 'fundhub_admin_editor-blocks',
        FH_ASSETS . '/js/fh_admin_editor-blocks.js',
        array( 'wp-blocks', 'wp-element' ),
        true
      );
    }

    add_action( 'enqueue_block_editor_assets', 'fh_enqueue_block_editor_assets' );


    // Enqueue backend assets ( General )
    function fh_admin_enqueue_scripts( $page )
    {
      if ( $page == 'edit.php' ) // && ( $_GET['post_type'] == 'asset_manager' )
      {
        wp_register_style( 'fundhub_admin_list-page',
          FH_ASSETS . '/css/fh_admin_list-page.css',
          array(),
          '1.0.0',
          'all'
        );
        wp_enqueue_style( 'fundhub_admin_list-page' );
      //wp_enqueue_script( 'fundhub_admin',
      //  FH_ASSETS . '/js/fh_admin.js',
      //  array(),
      //  '1.0.0',
      //   true
      //);
      }
      elseif ( $page == 'upload.php' )
      {
        wp_register_style( 'fundhub_admin_media-page',
          FH_ASSETS . '/css/fh_admin_media-page.css',
          array(),
          '1.0.0',
          'all'
        );
        wp_enqueue_style( 'fundhub_admin_media-page' );
        wp_enqueue_script( 'fundhub_admin_media-page',
          FH_ASSETS . '/js/fh_admin_media-page.js',
          false,
          '1.0.0',
          true
        );

      }
      elseif ( $page == 'widgets.php' )
      {
        wp_enqueue_media();
        wp_enqueue_script( 'fundhub_admin_widgets-page',
          FH_ASSETS . '/js/fh_admin_widgets-page.js',
          false,
          '1.0.0',
          true
        );
      }
    }

    add_action( 'admin_enqueue_scripts', 'fh_admin_enqueue_scripts', 10 );
}
else /* IS_FRONT */
{
    include FH_INCLUDES . '/fh_render_dynamic-blocks.php';
    include FH_INCLUDES . '/fh_render_shortcode_multisite-post.php';

    function fh_enqueue_scripts()
    {
      wp_deregister_script( 'wp-embed' );
      wp_register_script( 'fundhub',
        FH_ASSETS . '/js/fundhub.js',
        array(),
        '1.0.0',
        true
      );
      wp_enqueue_script( 'fundhub' );
    }

    add_action( 'wp_enqueue_scripts', 'fh_enqueue_scripts' );


    // Enqueue front-end styles
    function fh_enqueue_styles()
    {
      wp_dequeue_style( 'wp-block-library' );
      wp_register_style( 'fundhub',
        THEME_URI . '/style.css',
        array(),
        '1.0.0',
        'all'
      );
      wp_register_style( 'fundhub_ui_blocks',
        FH_ASSETS . '/css/fh_ui_blocks.css',
        array(),
        '1.0.0',
        'all'
      );
      wp_enqueue_style( 'fundhub' );
      wp_enqueue_style( 'fundhub_ui_blocks' );
    }

    add_action( 'wp_enqueue_scripts', 'fh_enqueue_styles' );
}

//---

// /* How to test for multi-site! */
// if ( ! is_multisite() ) {
// 	wp_die( __( 'Multisite support is not enabled.' ) );
// }

// Get base url of site...
// echo esc_url( get_home_url( $blog->userblog_id ) );
