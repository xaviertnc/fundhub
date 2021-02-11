<?php

class FH_Import_Data {

  public $site_url = '';
  public $theme_dir = '';
  public $import_path = 'content/import';
  public $uploads_info = array();


  function __construct( $site_url, $theme_dir, $import_path = null )
  {
    $this->site_url = $site_url;
    $this->theme_dir = $theme_dir;
    if ( isset( $import_path ) ) { $import_path = $import_path; }
    add_action( 'admin_post_fh_import_data', array( $this, 'import_all' ) );
  }


  function list_files_relative( $root_dir )
  {
    $files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator( $root_dir ),
    RecursiveIteratorIterator::LEAVES_ONLY
  );
  $relative_paths = array();
  $rel_offset = strlen( $root_dir ) + 1;
  foreach ( $files as $name => $file )
  {
      if ( ! $file->isDir() )
      {
    $file_path = $file->getRealPath();
        $relative_path = substr( $file_path, $rel_offset );
        $relative_paths[] = $relative_path;
      }
  }
  return $relative_paths;
  }


  function get_post_by_title( $post_title, $post_type = 'post', $output = OBJECT )
  {
    global $wpdb;
    $sql = "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type= %s";
    $post_id = $wpdb->get_var( $wpdb->prepare( $sql , $post_title, $post_type ) );
    echo '<pre>get_post_by_title: post_id = ', print_r( $post_id, true ), '</pre>';
    return $post_id ? get_post( $post_id, $output ) : null;
  }


  function map_terms( $terms )
  {
    $import = array();
    foreach ( $terms as $term )
    {
      $post = new stdClass();
      $post->term_id = $term->term_id;
      $post->name = $term->name;
      $post->slug = $term->slug;
      $post->description = $term->description;
      $post->parent = $term->parent;
      $import[] = $post;
    }
    return $import;
  }


  function map_menu_items( $menu_items )
  {
    $import = array();
    foreach ( $menu_items as $item )
    {
      $post = new stdClass();
      $post->post_id = $item->ID;
      $post->post_guid = $item->guid;
      $post->post_type = $item->post_type;
      $post->menu_order = $item->menu_order;
      $post->menu_item_parent = $item->menu_item_parent;
      $post->orig_object_id = $item->object_id;
      $post->object = $item->object;
      $post->url = $item->url;
      $post->title = $item->title;
      $post->target = $item->target;
      $post->attr_title = $item->attr_title;
      $post->description = $item->description;
      $post->classes = $item->classes;
      $import[] = $post;
    }
    return $import;
  }


  function map_post_attachments( $attachments )
  {
    $import = array();
    foreach ( $attachments as $attachment )
    {
      $post = new stdClass();
      $post->post_id = $attachment->ID;
      $post->post_guid = $attachment->guid;
      $post->post_author = $attachment->post_author;
      $post->post_parent = $attachment->post_parent;
      $post->post_name = $attachment->post_name;
      $post->post_title = $attachment->post_title;
      $post->post_excerpt = $attachment->post_excerpt;
      $post->post_mime_type = $attachment->post_mime_type;
      $post->post_metas = $attachment->post_metas;
      $import[] = $post;
    }
    return $import;
  }


  function import_taxonomy( $taxonomy, $taxonomy_basedir ) {}


  function import_nav_menus( $nav_menus_basedir ) {}


  function load_post( $post_dir = null )
  {
    $post = new stdClass();
    if ( empty( $post_dir ) ) { return; }
    $props_json = file_get_contents( "$post_dir/post.json" );
    $post->props = json_decode( $props_json );
    $post->content = file_get_contents( "$post_dir/content.html" );
    $post->media = $this->list_files_relative( "$post_dir/media" );
    return $post;
  }


  function map_loaded_post_props( $loaded_props, $type )
  {
    $mapped_props = new stdClass();

    if ( $type == 'insert' )
    {
      $mapped_props->post_name = $loaded_props->post_name;
      $mapped_props->post_title = $loaded_props->post_title;
      $mapped_props->post_author = $loaded_props->post_author;
    }

    $mapped_props->post_status = $loaded_props->post_status;
    $mapped_props->menu_order = $loaded_props->menu_order;

    if ( isset( $loaded_props->post_excerpt ) )
    {
      $mapped_props->post_excerpt = $loaded_props->post_excerpt;
    }

    if ( isset( $loaded_props->post_mime_type ) )
    {
      $mapped_props->post_mime_type = $loaded_props->post_mime_type;
    }

    // Meta values save as arrays with a single entry, even if they're scalar values.
    // Convert all array-type values to scalar values in "post_metas" to avoid a mess!
    $post_metas = $loaded_props->post_metas ? (array) $loaded_props->post_metas : array();
    $meta_input = array_map( function( $v ){ return is_array( $v ) ? reset( $v ) : $v; }, $post_metas );
    echo '<pre>meta_input = ', print_r( $meta_input, true ), '</pre>';
    if ( $meta_input )
    {
      $mapped_props->meta_input = $meta_input;
    }

    if ( isset( $meta_input[ '_thumbnail_id' ] ) )
    {
      $mapped_props[ 'thumbnail_id' ] = $meta_input[ '_thumbnail_id' ];
    }

    return $mapped_props;
  }


  function import_post( $existing_post, $loaded_data )
  {
    echo '<pre>import_post:import_data = ', print_r( $loaded_data, true ), '</pre>';

    $thumbnail_id = null;
    $orig_parent_post_id = $loaded_data->props->ID;
    $map_post_type = empty( $existing_post ) ? 'insert' : 'update';
    $mapped_post_props = $this->map_loaded_post_props( $loaded_data->props, $map_post_type );
    if ( isset( $mapped_props[ 'tumbnail_id' ] ) )
    {
      $thumbnail_id = $mapped_post_props[ 'tumbnail_id' ];
      unset( $mapped_post_props[ 'tumbnail_id' ] );
    }
    $mapped_post_props->post_content = $loaded_data->content;
    if ( $map_post_type == 'insert' )
    {
      $result = wp_insert_post( $mapped_post_props, 'wp_error:true' );
      echo '<pre>import_post:INSERT Result = ', print_r( $result, true ), '</pre>';
    }
    else /* map_post_type == 'update' */
    {
      $mapped_post_props->ID = $existing_post->ID;
      $result = wp_update_post( $mapped_post_props, 'wp_error:true' );
      echo '<pre>import_post:UPDATE Result = ', print_r( $result, true ), '</pre>';
    }
    if ( is_wp_error( $result ) )
    {
      $errors = $result->get_error_messages();
      foreach ( $errors as $error )
      {
        echo '<pre>import_post:error =', $error, '</pre>';
      }
    }
    $parent_post_id = $result;

    foreach ( $loaded_data->post_attachments as $attachment )
    {
      $post_type = $attachment->post_type;
      $post_title = $attachment->post_title;
      $att_is_thumbnail = ( $attachment->ID == $thumbnail_id );
      $existing_attachment = $this->get_post_by_title( $post_title, $post_type );
      $map_att_type = empty( $existing_attachment ) ? 'insert' : 'update';
      $mapped_att_props = $this->map_loaded_post_props( $attachment, $map_att_type );
      $mapped_att_props->post_parent = $parent_post_id;
      if ( $map_att_type == 'insert' )
      {
        $result = wp_insert_post( $mapped_att_props, 'wp_error:true' );
        echo '<pre>import_post_attachment:INSERT Result = ', print_r( $result, true ), '</pre>';
      }
      else /* $map_att_type == 'update' */
      {
        $mapped_att_props->ID = $existing_post->ID;
        $result = wp_update_post( $mapped_att_props, 'wp_error:true' );
        echo '<pre>import_post_attachment:UPDATE Result = ', print_r( $result, true ), '</pre>';
      }
      if ( is_wp_error( $result ) )
      {
        $errors = $result->get_error_messages();
        foreach ( $errors as $error )
        {
          echo '<pre>import_post_attachment:error =', $error, '</pre>';
        }
      }
      $attachment_id = $result;
      if ( $att_is_thumbnail )
      {
        update_post_meta( $parent_post_id, '_thumbnail_id', $attachment_id );
      }
    }

    foreach ( $loaded_data->other_attachments as $attachment )
    {

    }
  }


  function import_all()
  {
    // echo '<pre>REQUEST: ', print_r( $_REQUEST, true ), '</pre>';

    check_admin_referer( 'fh_nonce' );

    echo '<pre>SITE URL: ', print_r( $this->site_url, true ), '</pre>';
    echo '<pre>THEME DIR: ', print_r( $this->theme_dir, true ), '</pre>';
    echo '<pre>import PATH: ', print_r( $this->import_path, true ), '</pre>';

    /* import Directory */
    $import_basedir = $this->theme_dir . '/' . $this->import_path;
    echo '<pre>import DIR: ', print_r( $import_basedir, true ), '</pre>';

    /* Uploads Info */
    $this->uploads_info = wp_upload_dir();
    $this->uploads_info[ 'uploads' ] = untrailingslashit( str_replace(
      trailingslashit( $this->site_url ), '', $this->uploads_info[ 'baseurl' ] ) );
    echo '<pre>UPLOADS INFO: ', print_r( $this->uploads_info, true ), '</pre>';

    $page_dirs = glob( $import_basedir . '/page-posts/*' , GLOB_ONLYDIR );
    echo '<pre>PAGE DIRS: ', print_r( $page_dirs, true ), '</pre>';

    $asm_dirs = glob( $import_basedir . '/asm-posts/*' , GLOB_ONLYDIR );
    //echo '<pre>ASSET MANAGER DIRS: ', print_r( $asm_dirs, true ), '</pre>';

    $loaded_data = $this->load_post( $page_dirs[ 4 ] );
    //echo '<pre>IMPORTED POST DATA = ', print_r( $loaded_data, true ), '</pre>';
    $post_type = $loaded_data->props->post_type;
    $post_title = $loaded_data->props->post_title;
    $existing_post = $this->get_post_by_title( $post_title , $post_type );
    //echo '<pre>EXISTING POST = ', print_r( $existing_post, true ), '</pre>';
    $this->import_post( $existing_post, $loaded_data );

    //$asm = $this->load_post_files( $asm_dirs[ 10 ] );
    //echo '<pre>ASM 10: ', print_r( $asm, true ), '</pre>';

  }

} // end: FH_Import_Data


new FH_Import_Data( SITE_URL, THEME_DIR );


// ---


// echo '<pre>file_path: ', print_r( $file_path, true ), '</pre>';
// echo '<pre>relative_path: ', print_r( $relative_path, true ), '</pre>';
// $zip->addFile( $file_path, $relative_path );