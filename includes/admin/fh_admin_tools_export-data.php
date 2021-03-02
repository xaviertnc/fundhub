<?php

class FH_Export_Data {

  public $site_url = '';
  public $theme_dir = '';
  public $export_dir = '';
  public $export_path = 'content' . DIRECTORY_SEPARATOR . 'export';
  public $page_on_front_id = 0;
  public $uploads_info = array();


  function __construct( $site_url, $theme_dir, $export_path = null )
  {
    $this->site_url = $site_url;
    $this->theme_dir = $theme_dir;
    if ( isset( $export_path ) ) { $export_path = $export_path; }
    add_action( 'admin_post_fh_export_data', array( $this, 'export_all' ) );
  }


  function arg( $arr = null, $key = null, $default = null )
  {
    if ( ! $arr or ! $key ) { return; }
    return isset( $arr[$key] ) ? $arr[$key] : $default;
  }


  function fs_join( array $dir_parts, $postfix = null )
  {
    return implode( DIRECTORY_SEPARATOR, $dir_parts ) . $postfix;
  }


  function drop_keys( array $arr, array $drop_keys = [] )
  {
    return array_filter( $arr, function( $meta_key ) use ( $drop_keys ) {
      return ! in_array( $meta_key, $drop_keys );
    }, ARRAY_FILTER_USE_KEY );
  }


  function save_as_html( $file_path, $html )
  {
    return file_put_contents( $file_path, $html );
  }


  function save_as_json( $file_path, $data )
  {
    return file_put_contents( $file_path,
      json_encode( $data, JSON_PRETTY_PRINT ) );
  }


  function create_folder( $theme_relative_path )
  {
    return wp_mkdir_p( $theme_relative_path );
  }


  function get_taxonomy_terms( $taxonomy )
  {
    $terms = get_terms( array(
      'taxonomy' => $taxonomy,
      'hide_empty' => false,
    ) );
    return $terms;
  }


  function get_post_type( $post_type = 'post', $args = array() )
  {
    $default_args = array(
      'post_type'   => $post_type,
      'post_status' => null, // null => default == 'publish'
      'post_parent' => null, // null => any parent
      'numberposts' => 999
    );
    $posts = get_posts( array_merge( $default_args, $args ) );
    return $posts;
  }


  /**
   * If this function seems weird, it's because metadata values are stored
   * as arrays, even though we only want single values most of the time!
   */
  function add_post_metas( $posts )
  {
    foreach ( $posts as $post )
    {
      $post_metas = get_post_meta( $post->ID );
      $values_array = $this->arg( $post_metas, '_wp_attachment_metadata' );
      if ( $values_array )
      {
        $filtered_values_array = array();
        foreach ( $values_array ?: [] as $serialized_metas )
        {
          $metas = unserialize( $serialized_metas );
          //echo '<pre>add_post_metas:unserialized = ', print_r( $metas, true ), '</pre>';
          $metas[ 'image_meta' ] = array();
          $metas[ 'sizes' ] = array();
          //$serialized_metas = serialize( $metas );
          $filtered_values_array[] = $metas;
        }
        $post_metas[ '_wp_attachment_metadata' ] = $filtered_values_array;
      }
      unset( $post_metas[ '_wp_attachment_backup_sizes' ] );
      unset( $post_metas[ '_edit_lock' ] );
      $post->post_metas = $post_metas;
    }
    return $posts;
  }


  function filter_post_metas( $post_metas )
  {
    return array_filter( $post_metas, function( $key ) {
      return $key == '_thumbnail_id' or $key == '_wp_page_template' or
        strpos( $key, '_' ) !== 0; }, ARRAY_FILTER_USE_KEY );
  }


  function get_attached_media( $post )
  {
    // Empty string as first param == All Mime Types
    return get_attached_media( '', $post->ID );
  }


  function map_terms( $terms )
  {
    $export = array();
    foreach ( $terms as $term )
    {
      $export_item = new stdClass();
      $export_item->term_id = $term->term_id;
      $export_item->name = $term->name;
      $export_item->slug = $term->slug;
      $export_item->description = $term->description;
      $export_item->parent = $term->parent;
      // $export_item->filter = $term->filter;
      $export[] = $export_item;
    }
    return $export;
  }


  function map_menu_items( $menu_items )
  {
    $export = array();
    foreach ( $menu_items as $item )
    {
      $export_item = new stdClass();
      $export_item->post_id = $item->ID;
      $export_item->post_guid = $item->guid;
      $export_item->post_type = $item->post_type;
      $export_item->post_author = $item->post_author;
      $export_item->post_status = $item->post_status;
      $export_item->post_parent = $item->post_parent;
      $export_item->menu_order = $item->menu_order;
      $export_item->menu_item_parent = $item->menu_item_parent;
      $export_item->object_id = $item->object_id;
      $export_item->object = $item->object;
      $export_item->type = $item->type;
      $export_item->type_label = $item->type_label;
      $export_item->url = $item->url;
      $export_item->title = $item->title;
      $export_item->target = $item->target;
      $export_item->attr_title = $item->attr_title;
      $export_item->description = $item->description;
      $export_item->classes = $item->classes;
      // $export_item->filter = $item->filter;
      // $export_item->xfn = $item->xfn;
      $export[] = $export_item;
    }
    return $export;
  }


  function map_attachments( $attachments, $not_my_children = false )
  {
    $export = array();
    foreach ( $attachments as $attachment )
    {
      $export_item = new stdClass();
      $export_item->post_id = $attachment->ID;
      $export_item->post_guid = $attachment->guid;
      $export_item->post_type = $attachment->post_type;
      $export_item->post_author = $attachment->post_author;
      $export_item->post_status = $attachment->post_status;
      $export_item->post_parent = $attachment->post_parent;
      if ( $not_my_children and $attachment->post_parent > 0 )
      {
        $parent_post = get_post( $attachment->post_parent );
        $export_item->post_parent_title = $parent_post
          ? $parent_post->post_title : 'parent_not_found';
        $export_item->post_parent_type = $parent_post
          ? $parent_post->post_type : 'unknown';
      }
      $export_item->post_name = $attachment->post_name;
      $export_item->post_title = $attachment->post_title;
      $export_item->post_excerpt = $attachment->post_excerpt;
      $export_item->post_mime_type = $attachment->post_mime_type;
      $export_item->post_metas = $attachment->post_metas;
      $export[] = $export_item;
    }
    return $export;
  }


  function map_post_data( $post_data, $post )
  {
    $post_data->post_id = $post->ID;
    $post_data->post_guid = $post->guid;
    $post_data->post_type = $post->post_type;
    $post_data->post_author = $post->post_author;
    $post_data->post_status = $post->post_status;
    $post_data->post_parent = $post->post_parent;
    $post_data->post_name = $post->post_name;
    $post_data->post_title = $post->post_title;
    $post_data->menu_order = $post->menu_order;
    // $post_data->filter = $post->filter;
    return $post_data;
  }


  function get_unattached_media( $post_files = null )
  {
    $attachments = array();
    $other_attachment_ids = array();

    //echo '<pre>post_files: ', print_r( $post_files, true ), '</pre>';

    if ( empty( $post_files ) ) { return $attachments; }
    foreach ( $post_files as $file )
    {
      if ( $file->is_media_item and ! $file->is_attached_to_post )
      {
        $other_attachment_ids[] = $file->wp_attachment_id;
      }
    }
    if ( $other_attachment_ids )
    {
      $args = array(
        'post_type' => 'attachment',
        'post__in' => $other_attachment_ids
      );
      $attachments = get_posts( $args );

      //echo '<pre>attachments: ', print_r( $attachments, true ), '</pre>';

    }
    return $attachments;
  }


  /**
   * Cycle through each file HREF found inside $post->post_content
   * and determine if it's a local or external file href.
   * Also check if the href points inside the WP media library
   * and find its matching attachment post and ID if possible.
   *
   * PS: Duplicate HREFS are removed.
   */
  function extract_files_from_content( $post, $uploads_info )
  {
    $results = array();

    $regex = '/(?:src|href)="([^"]*\.(?:jpg|png|gif|pdf))"/i';
    preg_match_all( $regex, $post->post_content, $matches );

    if ( ! empty( $matches[1] ) )
    {
      $unique_hrefs = array();

      // Get raw hrefs while preventing duplicates
      foreach ( $matches[1] as $raw_file_href )
      {
        if ( !  in_array( $raw_file_href, $unique_hrefs ) )
        {
          $unique_hrefs[] = $raw_file_href;
        }
      }

      foreach ( $unique_hrefs as $raw_file_href )
      {
        $pos_http = strpos( $raw_file_href, 'http' );
        $pos_siteurl = strpos( $raw_file_href, $this->site_url );
        $is_external = ( ( $pos_http !== FALSE ) and ( $pos_siteurl === FALSE ) );

        $file_href = $raw_file_href;
        $wp_post_attachment = null;
        $wp_attachment_id = null;
        $is_media_item = false;

        // ( WP_CONTENT_DIR )
        if ( ! $is_external )
        {
          $uploads_href = ( $pos_http === FALSE ) ? $uploads_info[ 'uploads' ]
            : $uploads_info[ 'baseurl' ];

          if ( $uploads_href )
          {
            $uploads_href = trailingslashit( $uploads_href );
          }

          $is_media_item = ( $uploads_href and
            strpos( $raw_file_href, $uploads_href ) !== FALSE );

          if ( $is_media_item )
          {
            $file_href = str_replace( $uploads_href, '', $raw_file_href );
            $wp_attachment_id = FH_Lib::find_attachment_id( $file_href );
            $wp_post_attachment = FH_Lib::find_object_by( $post->post_attachments,
              'post_id', $wp_attachment_id ) ? 1 : 0;
          }
        }

        $result = new stdClass();
        $result->file = $file_href;
        $result->is_external = $is_external ? 1 : 0;
        $result->is_media_item = $is_media_item ? 1 : 0;
        $result->is_attached_to_post = $wp_post_attachment ? 1 : 0;
        $result->wp_attachment_id = $wp_attachment_id;
        $results[] = $result;
      }
    }
    return $results;
  }


  function export_widgets()
  {
    global $wpdb;
    $sql = "SELECT option_name, option_value FROM $wpdb->options
      WHERE option_name LIKE '%widget%'";
    $options = $wpdb->get_results( $sql );
    foreach ( $options?:array() as $option )
    {
      $option->option_value = unserialize( $option->option_value );
    }
    $widgets_basedir = $this->export_dir;
    if ( $this->create_folder( $widgets_basedir ) )
    {
      $file_path = $widgets_basedir . '/widgets.json';
      $this->save_as_json( $file_path, $options );
    }
  }


  function export_theme_mods()
  {
    $theme_options = new stdClass();
    $current_theme_slug = get_option( 'stylesheet' );
    $mods_option_name = 'theme_mods_' . $current_theme_slug;
    $theme_options->theme_mods_key = $mods_option_name;
    $theme_mods = get_option( $mods_option_name );
    echo '<pre>THEME MODS: ', print_r( $theme_mods, true ), '</pre>';
    $theme_options->theme_mods = $theme_mods;
    $theme_options->site_icon = get_option( 'site_icon' );
    $theme_options->header_scripts = get_option( 'fh_header_scripts' );
    $theme_options->noscripts = get_option( 'fh_noscripts' );
    $theme_options->footer_scripts = get_option( 'fh_footer_scripts' );
    $css_post_id = $this->arg( $theme_mods, 'custom_css_post_id' );
    $theme_options->custom_css = $css_post_id ? get_post( $css_post_id ) : null;
    $mods_basedir = $this->export_dir;
    if ( $this->create_folder( $mods_basedir ) )
    {
      $file_path = $mods_basedir . '/theme.json';
      $this->save_as_json( $file_path, $theme_options );
    }
  }


  function export_options()
  {
    $options = array(
      'siteurl'  => get_option( 'siteurl' ),
      'home'  => get_option( 'home' ),
      'blogname' => get_option( 'blogname' ),
      'blogdescription' => get_option( 'blogdescription' ),
      'upload_path' => get_option( 'upload_path' ),
      'WPLANG' => get_option( 'WPLANG', 'en_ZA' ),
      'permalink_structure' => get_option( 'permalink_structure' ),
      'uploads_use_yearmonth_folders' => get_option( 'uploads_use_yearmonth_folders' ),
      'wp_page_for_privacy_policy' => get_option( 'wp_page_for_privacy_policy' ),
      'thumbnail_crop' => get_option( 'thumbnail_crop' ),
      'ping_sites' => get_option( 'ping_sites' )
    );
    $options_basedir = $this->export_dir;
    if ( $this->create_folder( $options_basedir ) )
    {
      $file_path = $options_basedir . '/options.json';
      $this->save_as_json( $file_path, $options );
    }
  }


  function export_taxonomies()
  {

    $taxonomies = get_taxonomies( array(), 'objects' );
    $taxonomies = $this->drop_keys( $taxonomies?:[],
      array( 'post_tag', 'link_category', 'post_format' ) );
    foreach ( $taxonomies as $taxonomy => $props )
    {
      $taxonomy_terms = $this->get_taxonomy_terms( $taxonomy );
      $taxonomy_terms = $this->map_terms( $taxonomy_terms );
      $taxonomy_terms = FH_Lib::sort_objects_by( $taxonomy_terms, 'name' );
      $props->terms = $taxonomy_terms;
    }
    $taxonomy_basedir = $this->export_dir;
    if ( $this->create_folder( $taxonomy_basedir ) )
    {
      $file_path = $taxonomy_basedir . '/taxonomies.json';
      $this->save_as_json( $file_path, $taxonomies );
    }
  }


  function export_nav_menus()
  {
    $nav_menus = array();
    $nav_menu_terms = $this->get_taxonomy_terms( 'nav_menu' );
    $nav_menu_terms = FH_Lib::sort_objects_by( $nav_menu_terms, 'name' );
    foreach ( $nav_menu_terms as $nav_menu_term )
    {
      $nav_menu_items = wp_get_nav_menu_items( $nav_menu_term );
      $nav_menu_items = $this->map_menu_items( $nav_menu_items );
      $nav_menus[ $nav_menu_term->slug ] = $nav_menu_items;
    }
    $nav_menus_basedir = $this->export_dir;
    if ( $this->create_folder( $nav_menus_basedir ) )
    {
      $this->save_as_json( $nav_menus_basedir . '/navmenus.json', $nav_menus );
    }
  }


  function export_attachment_files( $attachment_posts, $files_basedir )
  {
    foreach ( $attachment_posts?:array() as $attachment )
    {
      $attached_file = reset( $attachment->post_metas[ '_wp_attached_file' ] );
      $rel_path = dirname( $attached_file );
      $dir_parts = array( $files_basedir );
      if ( $rel_path != '.' ) { $dir_parts[] = $rel_path; }
      $target_dir = $this->fs_join( $dir_parts );
      $uploas_url = $this->uploads_info[ 'baseurl' ];
      if ( $this->create_folder( $target_dir ) )
      {
        $online_file_url =  "$uploas_url/$attached_file";
        FH_Lib::copy_online_file( $online_file_url, $target_dir );
      }
    }
  }


  function export_general_media()
  {
    $dir_parts = array( $this->export_dir, 'unattached-media.json' );
    $unattached_data_file = $this->fs_join( $dir_parts );

    $unattached = $this->get_post_type( 'attachment', array( 'post_parent' => 0 ) );
    $unattached = $this->add_post_metas( $unattached );
    $unattached = $this->map_attachments( $unattached );
    //echo '<pre>UNATTACHED MEDIA: ', print_r( $unattached, true ), '</pre>';

    $this->save_as_json( $unattached_data_file, $unattached );

    $dir_parts = array( $this->export_dir, 'unattached-media' );
    $unattached_files_basedir = $this->fs_join( $dir_parts );

    $this->export_attachment_files( $unattached, $unattached_files_basedir );
  }


  function export_post_type( $post_type, $opts = array(), $query = array() )
  {
    $posts_basedir = $this->arg( $opts, 'dir', $this->export_dir );

    if ( empty( $query[ 'post_status' ] ) ) {
      $query[ 'post_status' ] = array( 'publish', 'private' );
    }

    $posts = $this->get_post_type( $post_type, $query );

    if ( $posts and $this->create_folder( $posts_basedir ) )
    {

      /* @TODO: Considder removing this line! Rather sort by query? */
      // $posts = FH_Lib::sort_objects_by( $posts, 'menu_order', 'numeric' );

      foreach ( $posts as $post )
      {
        $dir_parts = array( $posts_basedir, $post->post_name );
        $post_basedir = $this->fs_join( $dir_parts );

        if ( ! $this->create_folder( $post_basedir ) ) { continue; }

        /* Collect and Process Post / Page Data */
        $post_data = new stdClass();
        $post_attachments = $this->get_attached_media( $post );
        $post_attachments = $this->add_post_metas( $post_attachments );
        $post_data->post_attachments = $this->map_attachments( $post_attachments );
        $post_data->post_files = $this->extract_files_from_content( $post, $this->uploads_info );
        $other_attachments = $this->get_unattached_media( $post->post_files );
        $other_attachments = $this->add_post_metas( $other_attachments );
        $post_data->other_attachments = $this->map_attachments( $other_attachments, 'not-mine' );
        $post_data->post_metas = get_post_meta( $post->ID );
        $post_data->post_metas = $this->filter_post_metas( $post_data->post_metas );
        $this->map_post_data( $post_data, $post );
        $post_data->taxonomies = get_post_taxonomies( $post->ID );
        /* @TODO: Only "strategy" terms. Expand to cover ALL taxonomy terms..? */
        $post_data->terms = wp_get_post_terms( $post->ID, 'strategy' );
        if ( $post_type == 'page' and $post->ID == $this->page_on_front_id )
        {
          $post_data->is_frontpage = true;
        }

        /* Store Post Data */
        $dir_parts = array( $post_basedir, 'post.json' );
        $post_data_file = $this->fs_join( $dir_parts );
        $this->save_as_json( $post_data_file, $post_data );

        /* Store Post Content */
        $dir_parts = array( $post_basedir, 'content.html' );
        $content_file = $this->fs_join( $dir_parts );
        $this->save_as_html( $content_file, $post->post_content );

        /* Store Post Attachment Files ( WP Media Files ) */
        $dir_parts = array( $post_basedir, 'media' );
        $post_files_basedir = $this->fs_join( $dir_parts );
        if ( $this->create_folder( $post_files_basedir ) )
        {
          /* Copy Attached WP Media Files */
          $this->export_attachment_files( $post_data->post_attachments, $post_files_basedir );
          /* Copy Unattached WP Media Files */
          $this->export_attachment_files( $post_data->other_attachments, $post_files_basedir );

          // PS: Don't store files that's NOT on the domain host server.
          //  i.e. External file links.
        }
      }
    }
  }


  function export_all()
  {
    // echo '<pre>REQUEST: ', print_r( $_REQUEST, true ), '</pre>';

    check_admin_referer( 'fh_nonce' );

    echo '<pre>SITE URL: ', print_r( $this->site_url, true ), '</pre>';
    echo '<pre>THEME DIR: ', print_r( $this->theme_dir, true ), '</pre>';
    echo '<pre>EXPORT PATH: ', print_r( $this->export_path, true ), '</pre>';

    /* Export Directory */
    $dir_parts = array( $this->theme_dir, $this->export_path, 'export_' . time() );
    $this->export_dir = $this->fs_join( $dir_parts );
    echo '<pre>EXPORT DIR: ', print_r( $this->export_dir, true ), '</pre>';

    if ( ! $this->create_folder( $this->export_dir ) ) { return; }

    /* Uploads Info */
    $this->uploads_info = wp_get_upload_dir();
    $this->uploads_info[ 'uploads' ] = untrailingslashit( str_replace(
      trailingslashit( $this->site_url ), '', $this->uploads_info[ 'baseurl' ] ) );
    echo '<pre>UPLOADS INFO: ', print_r( $this->uploads_info, true ), '</pre>';

    $this->page_on_front_id = get_option( 'page_on_front', 0 );

    /* Options */
    $this->export_options();

    /* Widgets */
    $this->export_widgets();

    /* Theme Mods */
    $this->export_theme_mods();

    /* Taxonomies */
    $this->export_taxonomies();

    /* Nav Menus */
    $this->export_nav_menus();

    /* General Media Posts */
    $this->export_general_media();

    /* Page Posts */
    $dir = $this->fs_join( array( $this->export_dir, 'page-posts' ) );
    $this->export_post_type( 'page', array( 'dir' => $dir ) );

    /* Asset Manager Posts */
    $dir = $this->fs_join( array( $this->export_dir, 'asm-posts' ) );
    $query = array( 'orderby' => 'menu_order', 'order' => 'ASC' );
    $this->export_post_type( 'asset_manager', array( 'dir' => $dir ), $query );

    /* Wp Block Posts */
    $dir = $this->fs_join( array( $this->export_dir, 'wp-block-posts' ) );
    $this->export_post_type( 'wp_block', array( 'dir' => $dir ) );

  } // end: export_all

} // end: FH_Export_Data


new FH_Export_Data( SITE_URL, THEME_DIR );


//   $zip = new ZipArchive;
//   $zip->open( THEME_DIR . '/export.zip', ZipArchive::CREATE );
//   $files_to_zip = array();
//   $zip->addFile( THEME_DIR . '/export' );
//   $zip->close();