<?php

class FH_Export_Data {

  public $site_url = '';
  public $theme_dir = '';
  public $export_path = 'content/export';


  function __construct( $site_url, $theme_dir, $export_path = null )
  {
    $this->site_url = $site_url;
    $this->theme_dir = $theme_dir;
    if ( isset( $export_path ) ) { $export_path = $export_path; }
    add_action( 'admin_post_fh_export_data', array( $this, 'export_all' ) );
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
      // $export_item->post_author = $item->post_author;
      // $export_item->post_status = $item->post_status;
      // $export_item->post_parent = $item->post_parent;
      $export_item->menu_order = $item->menu_order;
      $export_item->menu_item_parent = $item->menu_item_parent;
      $export_item->orig_object_id = $item->object_id;
      $export_item->object = $item->object;
      // $export_item->type = $item->type;
      // $export_item->type_label = $item->type_label;
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


  function map_post_attachments( $attachments )
  {
    $export = array();
    foreach ( $attachments as $attachment )
    {
      $export_item = new stdClass();
      $export_item->post_id = $attachment->ID;
      $export_item->post_guid = $attachment->guid;
      //$export_item->post_type = $attachment->post_type;
      $export_item->post_author = $attachment->post_author;
      //$export_item->post_status = $attachment->post_status;
      $export_item->post_parent = $attachment->post_parent;
      $export_item->post_name = $attachment->post_name;
      $export_item->post_title = $attachment->post_title;
      $export_item->post_excerpt = $attachment->post_excerpt;
      $export_item->post_mime_type = $attachment->post_mime_type;
      $export_item->post_metas = $attachment->post_metas;
      $export[] = $export_item;
    }
    return $export;
  }


  function map_post( $page )
  {
    $export_item = new stdClass();
    $export_item->post_id = $page->ID;
    $export_item->post_guid = $page->guid;
    $export_item->post_type = $page->post_type;
    $export_item->post_author = $page->post_author;
    $export_item->post_status = $page->post_status;
    $export_item->post_parent = $page->post_parent;
    $export_item->post_name = $page->post_name;
    $export_item->post_title = $page->post_title;
    $export_item->post_metas = $page->post_metas;
    $export_item->post_files = $page->post_files;
    $export_item->post_attachments = $page->post_attachments;
    $export_item->other_attachments = $page->other_attachments;
    $export_item->menu_order = $page->menu_order;
    // $export_item->filter = $page->filter;
    return $export_item;
  }


  function get_unattached_media( $post_files = null )
  {
    $attachments = array();
    $other_attachment_ids = array();

    echo '<pre>post_files: ',
      print_r( $post_files, true ), '</pre>';

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
   * PS: Duplicate HREFS are removed before processing.
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
            $wp_attachment_id = fh_get_attachment_id( $file_href );
            $wp_post_attachment = fh_find_object_by( $post->post_attachments,
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


  function export_taxonomy( $taxonomy, $taxonomy_basedir )
  {
    $taxonomy_terms = fh_get_taxonomy_terms( $taxonomy );
    $taxonomy_terms = $this->map_terms( $taxonomy_terms );
    $taxonomy_terms = fh_sort_objects_by( $taxonomy_terms, 'name' );
    if ( fh_create_folder( $taxonomy_basedir ) )
    {
      $file_path = $taxonomy_basedir . '/' . $taxonomy . '.json';
      fh_save_as_json( $file_path, $taxonomy_terms );
    }
  }


  function export_nav_menus( $nav_menus_basedir )
  {
    $nav_menus = array();
    $nav_menu_terms = fh_get_taxonomy_terms( 'nav_menu' );
    $nav_menu_terms = fh_sort_objects_by( $nav_menu_terms, 'name' );
    foreach ( $nav_menu_terms as $nav_menu_term )
    {
      $nav_menu_items = wp_get_nav_menu_items( $nav_menu_term );
      $nav_menu_items = $this->map_menu_items( $nav_menu_items );
      $nav_menus[ $nav_menu_term->name ] = $nav_menu_items;
    }
    if ( fh_create_folder( $nav_menus_basedir ) )
    {
      fh_save_as_json( $nav_menus_basedir . '/navmenus.json', $nav_menus );
    }
  }


  function export_post( $post_type, $path )
  {
    $posts_basedir = "{$export_basedir}/{$path}";
    $posts = fh_get_post_type( $post_type );
    if ( $posts and fh_create_folder( $posts_basedir ) )
    {
      $posts = fh_sort_objects_by( $posts, 'menu_order', 'numeric' );
      foreach ( $posts as $post )
      {
        $post_basedir = "{$posts_basedir}/{$post->post_name}";

        if ( ! fh_create_folder( $post_basedir ) ) { continue; }

        fh_save_as_html( $post_basedir . '/content.html', $post->post_content );
        $post_attachments = fh_get_attached_media( $post );
        $post_attachments = fh_add_post_metas( $post_attachments );
        $post->post_attachments = $this->map_post_attachments( $post_attachments );
        $post->post_files = $this->extract_files_from_content( $post, $uploads_info );
        $other_attachments = $this->get_unattached_media( $post->post_files );
        $other_attachments = fh_add_post_metas( $other_attachments );
        $post->other_attachments = $this->map_post_attachments( $other_attachments );
        $post->post_metas = get_post_meta( $post->ID );
        $post->post_metas = fh_filter_post_metas( $post->post_metas );
        $post = $this->map_post( $post );

        fh_save_as_json( $post_basedir . '/post.json', $post );

        $files_basedir = $post_basedir . '/media';
        //echo '<pre>files_basedir: ', print_r( $files_basedir, true ), '</pre>';

        if ( fh_create_folder( $files_basedir ) )
        {
          if ( $post->post_attachments  )
          {
            foreach ( $post->post_attachments as $attachment )
            {
              $file = reset( $attachment->post_metas[ '_wp_attached_file' ] );
              //echo '<pre>file: ', print_r( $file, true ), '</pre>';
              $file_path = dirname( $file );
              //echo '<pre>file_path: ', print_r( $file_path, true ), '</pre>';
              $file_dir = $files_basedir . ( ($file_path != '.') ? '/' . $file_path : '' );
              if ( fh_create_folder( $file_dir ) )
              {
                //echo '<pre>file_dir: ', print_r( $file_dir, true ), '</pre>';
                //echo '<pre>from: ', print_r( $uploads_baseurl . '/' . $file, true ), '</pre>';
                //echo '<pre>to: ', print_r( $file_dir . '/' . basename( $file ), true ), '</pre>';
                fh_copy_file( $uploads_baseurl . '/' . $file, $file_dir );
              }
            }
          }
          if ( $post->other_attachments  )
          {
            foreach ( $post->other_attachments as $attachment )
            {
              $file = reset( $attachment->post_metas[ '_wp_attached_file' ] );
              $file_path = dirname( $file );
              $file_dir = $files_basedir . ( ($file_path != '.') ? '/' . $file_path : '' );
              if ( fh_create_folder( $file_dir ) )
              {
                fh_copy_file( $uploads_baseurl . '/' . $file, $file_dir );
              }
            }
          }
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
    $export_basedir = $this->theme_dir . '/' . $this->export_path . '/';
    $export_basedir .= time();
    echo '<pre>EXPORT DIR: ', print_r( $export_basedir, true ), '</pre>';

    /* Uploads Info */
    $uploads_info = wp_upload_dir();
    $uploads_baseurl = $uploads_info[ 'baseurl' ];
    $uploads_info[ 'uploads' ] = untrailingslashit( str_replace(
        trailingslashit( $this->site_url ), '', $uploads_baseurl ) );
    echo '<pre>UPLOADS INFO: ', print_r( $uploads_info, true ), '</pre>';

    /* Categories */
    $this->export_taxonomy( 'category', $export_basedir );

    /* Strategies */
    $this->export_taxonomy( 'strategy', $export_basedir );

    /* Nav Menus */
    $this->export_nav_menus( $export_basedir );

    /* Page Posts */
    $this->export_post( 'page', 'page-posts' );

    /* Asset Manager Posts */
    $this->export_post( 'asset_manager', 'asm-posts' );

  }

} // end: FH_Export_Data


new FH_Export_Data( SITE_URL, THEME_DIR );


//   $zip = new ZipArchive;
//   $zip->open( THEME_DIR . '/export.zip', ZipArchive::CREATE );
//   $files_to_zip = array();
//   $zip->addFile( THEME_DIR . '/export' );
//   $zip->close();