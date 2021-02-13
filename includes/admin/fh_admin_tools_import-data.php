<?php

class FH_Import_Data {

  public $site_url = '';
  public $theme_dir = '';
  public $orig_options = null;
  public $import_path = 'content/import';
  public $uploads_info = array();
  public $post_ids_map = array();


  function __construct( $site_url, $theme_dir, $import_path = null )
  {
    $this->site_url = $site_url;
    $this->theme_dir = $theme_dir;
    if ( isset( $import_path ) ) { $import_path = $import_path; }
    add_action( 'admin_post_fh_import_data', array( $this, 'import_all' ) );
  }


  function arg( $arr = null, $key = null, $default = null )
  {
    if ( ! $arr ) { return; }
    if ( ! $key ) { return $arr; }
    return isset( $arr[$key] ) ? $arr[$key] : $default;
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


  function import_taxonomies( $taxonomies )
  {
    // echo '<pre>import_taxonomies: ', print_r( $taxonomies, true ), '</pre>';
    $taxonomy_names = array_keys( (array) $taxonomies );
    echo '<pre>import_taxonomies: ', print_r( $taxonomy_names, true ), '</pre>';
    foreach ( (array) $taxonomies as $taxonomy_name => $taxonomy )
    {
      /**
       * @TODO: We need to keep a term_ids_map array and resolve parent_ids
       * against this list for hieracrhical terms.
       */
      foreach ( $taxonomy->terms as $term )
      {
        echo '<pre>term: ', print_r( $term->name, true ), '</pre>';
        $args = array('slug' => $term->slug );
        if ( $term->description ) { $args[ 'description' ] = $term->description; }
        if ( $term->parent > 0 ) { $args[ 'parent' ] = $term->parent; }
        // if ( term_exists( $term->name, $taxonomy_name, null/*parent*/ ) ) { continue; }
        wp_insert_term( $term->name, $taxonomy_name, $args );
      }
    }
  }


  function import_nav_menus( $nav_menus_basedir ) {}


  function load_post_props( $post_dir = null )
  {
    if ( empty( $post_dir ) ) { return; }
    $props_json = file_get_contents( "$post_dir/post.json" );
    $props = json_decode( $props_json );
    if ( $props ) { $props->basedir = $post_dir; }
    return $props;
  }


  function load_post_data( $post_props )
  {
    $post = new stdClass();
    $post_dir = $post_props->basedir;
    unset( $post_props->basedir );
    $post->props = $post_props;
    $post->content = file_get_contents( "$post_dir/content.html" );
    $post->media_dir = "$post_dir/media";
    $post->media = $this->list_files_relative( $post->media_dir );
    return $post;
  }


  function map_post_props( $loaded_props, $map_type )
  {
    $mapped_props = new stdClass();

    if ( $map_type == 'insert' )
    {
      $mapped_props->post_name = $loaded_props->post_name;
      $mapped_props->post_title = $loaded_props->post_title;
      $mapped_props->post_author = $loaded_props->post_author;
      $mapped_props->post_parent = $loaded_props->post_parent;
      if ( isset( $loaded_props->post_type ) )
      {
        $mapped_props->post_type = $loaded_props->post_type;
      }
    }

    if ( isset( $loaded_props->post_status ) )
    {
      $mapped_props->post_status = $loaded_props->post_status;
    }

    if ( isset( $loaded_props->menu_order ) )
    {
      $mapped_props->menu_order = $loaded_props->menu_order;
    }

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
    $post_metas = $loaded_props->post_metas
      ? (array) $loaded_props->post_metas : array();
    $meta_input = array_map( function( $v ){ return is_array( $v )
      ? reset( $v ) : $v; }, $post_metas );
    echo '<pre>meta_input = ', print_r( $meta_input, true ), '</pre>';
    if ( $meta_input )
    {
      if ( isset( $meta_input[ '_wp_attached_file' ] ) )
      {
        $mapped_props->file = $meta_input[ '_wp_attached_file' ];
      }
      if ( isset( $meta_input[ '_wp_attachment_metadata' ] ) )
      {
        $mapped_props->att_metas = $meta_input[ '_wp_attachment_metadata' ];
        unset( $meta_input[ '_wp_attachment_metadata' ] );
      }
      if ( isset( $meta_input[ '_thumbnail_id' ] ) )
      {
        $mapped_props->thumbnail_id = $meta_input[ '_thumbnail_id' ];
      }
      unset( $meta_input[ '_edit_lock' ] );
      $mapped_props->meta_input = $meta_input;
    }

    return $mapped_props;
  }


  function import_post( $post_props )
  {
    $loaded_data = $this->load_post_data( $post_props );
    $post_type = $loaded_data->props->post_type;
    $post_title = $loaded_data->props->post_title;
    $existing_post = $this->get_post_by_title( $post_title , $post_type );

    echo '<pre>import_post:import_data = ',
      print_r( $loaded_data, true ), '</pre>';

    $thumbnail_id = null;
    $orig_parent_post_id = $loaded_data->props->post_id;
    $map_post_type = empty( $existing_post ) ? 'insert' : 'update';
    $mapped_props = $this->map_post_props( $loaded_data->props, $map_post_type );
    echo '<pre>import_post:mapped_props = ', print_r( $mapped_props, true ), '</pre>';

    if ( isset( $mapped_props->thumbnail_id ) )
    {
      $thumbnail_id = $mapped_props->thumbnail_id;
      unset( $mapped_props->thumbnail_id );
    }
    $mapped_props->post_content = $loaded_data->content;
    if ( $map_post_type == 'insert' )
    {
      if ( $mapped_props->post_parent > 0 )
      {
        $mapped_props->post_parent = $this->arg(
          $this->post_ids_map, $mapped_props->post_parent, 0 );
      }
      $post_id = wp_insert_post( $mapped_props, 'wp_error:true' );
      echo '<pre>import_post:INSERT Result = ',
        print_r( $post_id, true ), '</pre>';
    }
    else /* map_post_type == 'update' */
    {
      $mapped_props->ID = $existing_post->ID;
      $post_id = wp_update_post( $mapped_props, 'wp_error:true' );
      echo '<pre>import_post:UPDATE Result = ',
        print_r( $post_id, true ), '</pre>';
    }
    if ( is_wp_error( $post_id ) )
    {
      $errors = $post_id->get_error_messages();
      foreach ( $errors as $error )
      {
        echo '<pre>import_post:error =', $error, '</pre>';
      }
    }
    $parent_post_id = $post_id;
    $this->post_ids_map[ $orig_parent_post_id ] = $parent_post_id;

    $all_attachments = array_merge(
      $loaded_data->props->post_attachments,
      $loaded_data->props->other_attachments
     );

    foreach ( $all_attachments as $attachment )
    {
      $file_path_rel = null;
      $post_title = $attachment->post_title;
      $att_is_thumbnail = ( $attachment->post_id == $thumbnail_id );
      $att_is_not_child = $attachment->post_parent != $orig_parent_post_id;
      $existing_attachment = $this->get_post_by_title( $post_title, 'attachment' );
      $map_att_type = empty( $existing_attachment ) ? 'insert' : 'update';
      $mapped_att_props = $this->map_post_props( $attachment, $map_att_type );
      $mapped_att_props->post_parent = $parent_post_id;
      echo '<pre>import_post_attachment:props = ',
          print_r( $mapped_att_props, true ), '</pre>';
      echo '<pre>import_post_attachment:attachment->post_id = ',
          print_r( $attachment->post_id, true ), '</pre>';
      echo '<pre>import_post_attachment:thumbnail_id = ',
          print_r( $thumbnail_id, true ), '</pre>';
      // Get Attacment Image Metas
      if ( isset( $mapped_att_props->att_metas ) )
      {
        $att_metas = $mapped_att_props->att_metas;
        unset( $mapped_att_props->att_metas );
      }
      // Get Attacment Image File
      if ( isset( $mapped_att_props->file ) )
      {
        $file_path_rel = $mapped_att_props->file;
        unset( $mapped_att_props->file );
      }
      if ( $att_is_not_child )
      {
        $att_parent_post = ( $attachment->post_parent > 0 )
          ? $this->get_post_by_title($attachment->post_parent_title,
             $attachment->post_parent_type )
          : null;
         $mapped_att_props->post_parent = $att_parent_post
           ? $att_parent_post->ID : 0;
      }
      // INSERT Attachment
      if ( $map_att_type == 'insert' )
      {
        $mapped_att_props->post_type = 'attachment';
        $result = wp_insert_post( $mapped_att_props, 'wp_error:true' );
        echo '<pre>import_post_attachment:INSERT new_attachment_id = ',
          print_r( $result, true ), '</pre>';
      }
      // UPDATE Attachment
      else /* $map_att_type == 'update' */
      {
        $mapped_att_props->ID = $existing_attachment->ID;
        $result = wp_update_post( $mapped_att_props, 'wp_error:true' );
        echo '<pre>import_post_attachment:UPDATE attachment_id = ',
          print_r( $result, true ), '</pre>';
      }
      if ( is_wp_error( $result ) )
      {
        $errors = $result->get_error_messages();
        foreach ( $errors as $error )
        {
          echo '<pre>import_post_attachment:error =', $error, '</pre>';
        }
      }
      $new_attachment_id = $result;
      $this->post_ids_map[ $attachment->post_id ] = $new_attachment_id;

      if ( $att_metas )
      {
        echo '<pre>import_post_attachment:att_metas = ',
          print_r( $att_metas, true ), '</pre>';
        wp_update_attachment_metadata( $new_attachment_id, (array) $att_metas );
      }
      // Set the parent post thumbnail ID if this attachment is it's thumnail.
      if ( $att_is_thumbnail )
      {
        update_post_meta( $parent_post_id, '_thumbnail_id', $new_attachment_id );
        echo '<pre>import_post:SET_THUMBNAIL_ID = ',
          print_r( $new_attachment_id, true ), '</pre>';
      }
      if ( $file_path_rel )
      {
        $imports_path = $loaded_data->media_dir . '/' . $file_path_rel;
        $uploads_path = $this->uploads_info[ 'path' ] . '/' . $file_path_rel;
        echo '<pre>import_post:imports_file_path = ',
          print_r( $imports_path, true ), '</pre>';
        echo '<pre>import_post:uploads_file_path = ',
          print_r( $uploads_path, true ), '</pre>';
        if ( !file_exists( $uploads_path ) and file_exists( $imports_path ) )
        {
          wp_mkdir_p( dirname( $uploads_path ) ); // @return true == Dir exists
          echo @copy( $imports_path, $uploads_path )
            ? '<pre>import_post:copy_file = OK</pre>'
            : '<pre>import_post:copy_file = FAILED</pre>';
        }
        else
        {
          echo '<pre>import_post:copy_file = SKIP</pre>';
        }
      }
    }
  }


  function migrate_content()
  {
    foreach ( $this->post_ids_map as $orig_post_id => $new_post_id )
    {
      $post = get_post( $new_post_id );
      if ( $post )
      {
        echo '<pre>migrate_content:orig_post_id = ', print_r( $orig_post_id, true ), '</pre>';
        echo '<pre>migrate_content:new_post_id = ', print_r( $new_post_id, true ), '</pre>';
        $raw_content = $post->post_content;
        $orig_siteurl = $this->orig_options->siteurl;
        $content = str_replace($orig_siteurl, $this->site_url, $raw_content );
        preg_match_all( '/{"ref":(\d+)}/', $content, $matches );
        echo '<pre>migrate_content:matches = ', print_r( $matches, true ), '</pre>';
        if ( $matches )
        {
          $finds = $matches[0];
          $ids = $matches[1];
          $replaces = array();
          foreach( $ids as $orig_id )
          {
            $new_id = $this->arg( $this->post_ids_map, $orig_id );
            if ( $new_id ) { $replaces[] = '{"ref":' . $new_id . '}'; }
          }
          $content = str_replace( $finds, $replaces, $content );
        }
        preg_match_all( '/image {"id":(\d+)/', $content, $matches );
        echo '<pre>migrate_content:matches = ', print_r( $matches, true ), '</pre>';
        if ( $matches )
        {
          $finds = $matches[0];
          $ids = $matches[1];
          $replaces = array();
          foreach( $ids as $orig_id )
          {
            $new_id = $this->arg( $this->post_ids_map, $orig_id );
            if ( $new_id ) { $replaces[] = 'image {"id":' . $new_id; }
          }
          $content = str_replace( $finds, $replaces, $content );
        }
        preg_match_all( '/wp-image-(\d+)/', $content, $matches );
        echo '<pre>migrate_content:matches = ', print_r( $matches, true ), '</pre>';
        if ( $matches )
        {
          $finds = $matches[0];
          $ids = $matches[1];
          $replaces = array();
          foreach( $ids as $orig_id )
          {
            $new_id = $this->arg( $this->post_ids_map, $orig_id );
            if ( $new_id ) { $replaces[] = 'wp-image-' . $new_id; }
          }
          $content = str_replace( $finds, $replaces, $content );
        }
        $post_id = wp_update_post( array(
          'ID' => $new_post_id,
          'post_content' => $content
        ), false );
        if ( is_wp_error( $post_id ))
        {
          $errors = $post_id->get_error_messages();
          foreach ( $errors as $error )
          {
            echo '<pre>migrate_content:error =', $error, '</pre>';
          }
        }
      }
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

    /* Source WP Options */
    $options_json = file_get_contents( "$import_basedir/options.json" );
    $this->orig_options = json_decode( $options_json );
    echo '<pre>ORIG WP OPTIONS: ', print_r( $this->orig_options, true ), '</pre>';

    /* Taxonomies */
    $taxonomies_json = file_get_contents( "$import_basedir/taxonomies.json" );
    $this->import_taxonomies( json_decode( $taxonomies_json ) );

    /* Uploads Info */
    $this->uploads_info = wp_upload_dir();
    $this->uploads_info[ 'uploads' ] = untrailingslashit( str_replace(
      trailingslashit( $this->site_url ), '', $this->uploads_info[ 'baseurl' ] ) );
    echo '<pre>UPLOADS INFO: ', print_r( $this->uploads_info, true ), '</pre>';

    $post_type_dirs = glob( $import_basedir . '/*' , GLOB_ONLYDIR );
    echo '<pre>POST TYPE DIRS: ', print_r( $post_type_dirs, true ), '</pre>';

    $post_dirs = array();

    foreach( $post_type_dirs as $post_type_dir )
    {
      $type_dirs = glob( "$post_type_dir/*" , GLOB_ONLYDIR );
      $post_dirs = array_merge( $post_dirs, $type_dirs );
    }

    echo '<pre>POST DIRS: ', print_r( $post_dirs, true ), '</pre>';

    $top_level_posts = array();
    $child_posts = array();

    foreach ( $post_dirs as $post_dir )
    {
      $post_props = $this->load_post_props( $post_dir );
      if ( ! $post_props ) { continue; }
      if ( $post_props->post_parent > 0 )
      {
        $child_posts[] = $post_props;
      }
      else
      {
        $top_level_posts[] = $post_props;
      }
    }

    /* Load Top Level ( Parent ) Posts First. */
    foreach ( $top_level_posts as $post_props )
    {
      $this->import_post( $post_props );
    }

    foreach ( $child_posts as $post_props )
    {
      $this->import_post( $post_props );
    }

    echo '<pre>POST IDS MAP: ', print_r( $this->post_ids_map, true ), '</pre>';

    $this->migrate_content();

  }

} // end: FH_Import_Data


new FH_Import_Data( SITE_URL, THEME_DIR );


// ---


// echo '<pre>file_path: ', print_r( $file_path, true ), '</pre>';
// echo '<pre>relative_path: ', print_r( $relative_path, true ), '</pre>';
// $zip->addFile( $file_path, $relative_path );