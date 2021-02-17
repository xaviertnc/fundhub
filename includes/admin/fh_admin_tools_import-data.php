<?php

class FH_Import_Data {

  public $site_url = '';
  public $theme_dir = '';
  public $import_dir = '';
  public $import_path = 'content/import';
  public $uploads_info = array();
  public $post_ids_map = array();
  public $orig_options = null;


  function __construct( $site_url, $theme_dir, $import_path = null )
  {
    $this->site_url = $site_url;
    $this->theme_dir = $theme_dir;
    if ( isset( $import_path ) ) { $import_path = $import_path; }
    add_action( 'admin_post_fh_import_data', array( $this, 'import_all' ) );
  }


  function arg( $arr = null, $key = null, $default = null )
  {
    if ( ! $arr or ! $key ) { return; }
    return isset( $arr[$key] ) ? $arr[$key] : $default;
  }


  function tree_array( array $array, $id_prop, $parent_prop )
  {
    $depth = 0;
    $tree = array();
    $index = array();
    $remaining = array();

    // Initialize tree
    foreach ( $array as $item )
    {
      $item->children = array();
      $id = $item->$id_prop;

      // If this is a top-level node, add it to the tree immediately
      if ( empty( $item->$parent_prop ) )
      {
        $index[ $id ] = $item;
        $tree[] =& $index[ $id ];
      }
      // If this isn't a top-level node, we have to process it later
      else
      {
        $remaining[ $id ] = $item;
      }
    }

    // Process all 'remaining' nodes
    // Check 'depth' to prevent bad things from happening. :0)
    while ( $remaining and $depth < 10 )
    {
      foreach( $remaining as $item )
      {
        $id = $item->$id_prop;
        $pid = $item->$parent_prop;
        // If the parent has already been added to the tree, it's
        // safe to add this node too
        if ( isset ( $index[ $pid ] ) )
        {
          $index[ $id ] = $item;
          $index[ $pid ]->children[] =& $index[ $id ];
          unset( $remaining[ $id ] );
        }
      }
      $depth += 1;
    }

    return $tree;
  }


  function extract_term_names( $taxonomy, array $terms )
  {
    $result = array_filter( $terms,
      function( $term ) use ( $taxonomy ) { return $term->taxonomy == $taxonomy; }
    );
    return $result
      ? array_map( function( $term ) { return $term->name; }, $result )
      : array();
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
    // echo '<pre>get_post_by_title: post_id = ', print_r( $post_id, true ), '</pre>';
    return $post_id ? get_post( $post_id, $output ) : null;
  }


  function migrate_urls( $content, $is_json_string = false )
  {
    $replace_url = $this->orig_options->siteurl;
    $with_url = $this->site_url;
    if ( $is_json_string )
    {
      $replace_url = str_replace( '/', '\\/', $replace_url );
      $with_url = str_replace( '/', '\\/', $with_url );
    }
    $output = str_replace( $replace_url, $with_url, $content );
    //echo '<pre>Migrate output = ', print_r( $output, true ), '</pre>';
    return $output;
  }


  function migrate_post_content()
  {
    foreach ( $this->post_ids_map as $orig_post_id => $new_post_id )
    {
      $post = get_post( $new_post_id );
      if ( $post )
      {
        //echo '<pre>migrate_content:orig_post_id = ', print_r( $orig_post_id, true ), '</pre>';
        //echo '<pre>migrate_content:new_post_id = ', print_r( $new_post_id, true ), '</pre>';
        $content = $this->migrate_urls( $post->post_content );
        if ( empty( $content ) ) { continue; }
        preg_match_all( '/{"ref":(\d+)}/', $content, $matches );
        //echo '<pre>migrate_content:matches = ', print_r( $matches, true ), '</pre>';
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
        //echo '<pre>migrate_content:matches = ', print_r( $matches, true ), '</pre>';
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
        //echo '<pre>migrate_content:matches = ', print_r( $matches, true ), '</pre>';
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


  function import_options()
  {
    echo '<pre>import_options:start... </pre>';
    $options_json = file_get_contents( "$this->import_dir/options.json" );
    $this->orig_options = json_decode( $options_json );
    foreach ( $this->orig_options as $option_name => $option_value )
    {
      if ( in_array( $option_name, array( 'siteurl', 'home' ) ) ) { continue; }
      update_option( $option_name, $option_value );
    }
    // echo '<pre>ORIG WP OPTIONS: ', print_r( $this->orig_options, true ), '</pre>';
  }


  function import_theme_options()
  {
    echo '<pre>import_theme_options:start... </pre>';
    $theme_options_json = file_get_contents( "$this->import_dir/theme.json" );
    $theme_options = json_decode( $theme_options_json );
    // echo '<pre>THEME OPTIONS: ', print_r( $theme_options, true ), '</pre>';
    if ( ! $theme_options ) { return; }
    if ( isset( $theme_options->theme_mods ) )
    {
      $theme_mods = $theme_options->theme_mods;
      // echo '<pre>THEME MODS: ', print_r( $theme_mods, true ), '</pre>';
      $logo_post_id = isset( $theme_mods->custom_logo ) ? $theme_mods->custom_logo : null;
      // echo '<pre>ORIG SITE LOGO POST ID: ', print_r( $logo_post_id, true ), '</pre>';
      $current_mods = get_option( $theme_options->theme_mods_key );
      // echo '<pre>CURRENT MODS: ', print_r( $current_mods, true ), '</pre>';
      if ( isset( $this->post_ids_map[ $logo_post_id ] ) ) {
        $current_mods[ 'custom_logo' ] = $this->post_ids_map[ $logo_post_id ];
      }
      update_option( $theme_options->theme_mods_key, $current_mods );
    }
    if ( isset( $theme_options->site_icon ) )
    {
      $orig_site_icon_id = $theme_options->site_icon;
      // echo '<pre>ORIG SITE ICON POST ID: ', print_r( $orig_site_icon_id, true ), '</pre>';
      if ( isset( $this->post_ids_map[ $orig_site_icon_id ] ) ) {
        $site_icon_id = $this->post_ids_map[ $orig_site_icon_id ];
        update_option( 'site_icon', $site_icon_id );
      }
    }
    if ( isset( $theme_options->header_scripts ) )
    {
      $scripts = $theme_options->header_scripts;
      // echo '<pre>HEADER SCRIPTS: ', htmlentities( $scripts ), '</pre>';
      update_option( 'fh_header_scripts', $scripts );
    }
    if ( isset( $theme_options->noscripts ) )
    {
      $noscripts = $theme_options->noscripts;
      // echo '<pre>NO SCRIPTS: ', htmlentities( $noscripts ), '</pre>';
      update_option( 'fh_noscripts', $noscripts );
    }
    if ( isset( $theme_options->footer_scripts ) )
    {
      $scripts = $theme_options->footer_scripts;
      // echo '<pre>FOOTER SCRIPTS: ', htmlentities( $scripts ), '</pre>';
      update_option( 'fh_footer_scripts', $scripts );
    }
    if ( isset( $theme_options->custom_css ) )
    {
      $css = $theme_options->custom_css->post_content;
      // $css = $this->migrate_urls( $css );
      // echo '<pre>CUSTOM CSS: ', htmlentities( $css ), '</pre>';
      wp_update_custom_css_post( $css );
    }
  }


  function delete_widget_options()
  {
    global $wpdb;
    echo '<pre>delete_widget_options... </pre>';
    $widget_opt_names = $wpdb->get_col( "SELECT option_name FROM $wpdb->options
      WHERE option_name LIKE '%widget%'" );
    foreach ( $widget_opt_names as $option_name )
    {
      // echo '<pre>delete: ', print_r( $option_name, true), '</pre>';
      delete_option( $option_name );
    }
  }


  function import_widgets()
  {
    echo '<pre>import_widgets:start... </pre>';
    $widgets_json = file_get_contents( "$this->import_dir/widgets.json" );
    $widgets_json = $this->migrate_urls( $widgets_json, true );
    $widget_options = json_decode( $widgets_json, true );
    if ( ! $widget_options ) { return; }
    $this->delete_widget_options();
    foreach ( $widget_options?:array() as $w )
    {
      // echo '<pre>widget:name = ', print_r( $w[ 'option_name' ], true ), '</pre>';
      // echo '<pre>widget:value = ', print_r( $w[ 'option_value' ], true ), '</pre>';
      update_option( $w[ 'option_name' ], $w[ 'option_value' ], true );
    }
  }


  function import_taxonomies()
  {
    echo '<pre>import_taxonomies:start... </pre>';
    $taxonomies_json = file_get_contents( "$this->import_dir/taxonomies.json" );
    $taxonomies = json_decode( $taxonomies_json );
    // echo '<pre>import_taxonomies: ', print_r( $taxonomies, true ), '</pre>';
    $taxonomy_names = array_keys( (array) $taxonomies );
    //echo '<pre>import_taxonomies: ', print_r( $taxonomy_names, true ), '</pre>';
    foreach ( (array) $taxonomies as $taxonomy_name => $taxonomy )
    {
      /**
       * @TODO: We need to keep a term_ids_map array and resolve parent_ids
       * against this list for hieracrhical terms.
       */
      foreach ( $taxonomy->terms as $term )
      {
        //echo '<pre>term: ', print_r( $term->name, true ), '</pre>';
        $args = array('slug' => $term->slug );
        if ( $term->description ) { $args[ 'description' ] = $term->description; }
        if ( $term->parent > 0 ) { $args[ 'parent' ] = $term->parent; }
        wp_insert_term( $term->name, $taxonomy_name, $args );
      }
    }
  }


  function delete_menu_items( $menu_id )
  {
    global $wpdb;
    echo '<pre>delete_menu_items:start... menu = ', $menu_id, '</pre>';
    $menu_items = wp_get_nav_menu_items( $menu_id );
    // echo '<pre>DELETE MENU ITEMS menu-', $menu_id, ': ', print_r( $menu_items, true ), '</pre>';
    foreach ( (array) $menu_items as $menu_item ) { wp_delete_post( $menu_item->ID, true ); }
    // $wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $menu_id ) );
  }


  function add_menuitem_recursive( $menu_id, $item )
  {
    $object_id = $this->arg( $this->post_ids_map, $item->object_id );
    $args = array(
      'menu-item-object-id'   => $object_id,
      'menu-item-object'      => $item->object,
      'menu-item-type'        => $item->type,
      'menu-item-status'      => $item->post_status,
      'menu-item-classes'     => implode( ' ', $item->classes ),
      'menu-item-position'    => $item->menu_order,
      'menu-item-description' => $item->description,
      'menu-item-attr-title'  => $item->attr_title,
      'menu-item-target'      => $item->target,
      'menu-item-title'       => $item->title,
      'menu-item-url'         => $item->url ? $this->migrate_urls( $item->url ): ''
    );
    //echo '<pre>ADD MENU-', $menu_id, ' ITEM: ', print_r( $args, true ), '</pre>';
    $item_post_id = wp_update_nav_menu_item( $menu_id, 0, $args );
    //echo '<pre>ITEM ID: ', print_r( $item_post_id, true ), '</pre>';
    if ( is_wp_error( $item_post_id ) ) { return; }
    foreach ( $item->children?:[] as $child_item )
    {
      $this->add_menuitem_recursive( $menu_id, $child_item );
    }
  }


  function import_nav_menus()
  {
    echo '<pre>import_nav_menus:start... </pre>';
    $navmenus_json = file_get_contents( "$this->import_dir/navmenus.json" );
    $navmenus = json_decode( $navmenus_json );
    if ( $navmenus ) { $navmenus = (array) $navmenus; } else { return; }
    $menu_locations_map = array();
    foreach ( $navmenus as $menu => $menu_items )
    {
      $menu_obj = get_term_by( 'name', $menu, 'nav_menu' );
      $menu_id = $menu_obj->term_id;
      $this->delete_menu_items( $menu_id );
      $menu_locations_map[ $menu ] = $menu_id;
      //echo '<pre>NAV MENU ', $menu, ': ', print_r( $menu_obj, true ), '</pre>';
      $tree = $this->tree_array( $menu_items, 'post_id', 'menu_item_parent' );
      //echo '<pre>ITEMS TREE: ', print_r( $tree, true ), '</pre>';
      foreach ( $tree as $item )
      {
        $this->add_menuitem_recursive( $menu_id, $item );
      }
    }
    if ( $menu_locations_map )
    {
      set_theme_mod( 'nav_menu_locations', $menu_locations_map );
    }
  }


  function import_unattached_media()
  {
    echo '<pre>import_unattached_media:start... </pre>';
    $unattached_json = file_get_contents( "$this->import_dir/unattached.json" );
    $media_dir = "$this->import_dir/unattached-media";
    $unattached = json_decode( $unattached_json );
    foreach ( $unattached as $attachment )
    {
      $new_attachment_id = $this->import_attachment( $attachment, $media_dir );
      // echo '<pre>import_unattached_media:new_attachment_id = ',
      //   print_r( $new_attachment_id, true ), '</pre>';
    }
  }


  function import_posts()
  {
    echo '<pre>import_posts:start... </pre>';

    $post_type_dirs = glob( $this->import_dir . '/*' , GLOB_ONLYDIR );
    $post_type_dirs = array_filter( $post_type_dirs, function( $dir ) {
      return strpos( $dir, 'unattached' ) === FALSE; } );

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

    /* Load Top Level Posts ( Parents ) First. */
    foreach ( $top_level_posts as $post_props )
    {
      $this->import_post( $post_props );
    }

    /* Load Child Posts. */
    foreach ( $child_posts as $post_props )
    {
      $this->import_post( $post_props );
    }

    /* Replace site url and source post-ID references. */
    $this->migrate_post_content();
  }


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
    // echo '<pre>meta_input = ', print_r( $meta_input, true ), '</pre>';
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


  function import_attachment( $attachment, $media_dir, $orig_parent_post_id = -1 )
  {
    // echo '<pre>import_attachment:orig_parent_post_id = ',
    //     print_r( $orig_parent_post_id, true ), '</pre>';
    // echo '<pre>import_attachment:attachment = ',
    //     print_r( $attachment, true ), '</pre>';
    $att_metas = null;
    $file_path_rel = null;
    $post_title = $attachment->post_title;
    $att_is_not_child = $attachment->post_parent != $orig_parent_post_id;
    $existing_attachment = $this->get_post_by_title( $post_title, 'attachment' );
    $map_att_type = empty( $existing_attachment ) ? 'insert' : 'update';
    $mapped_att_props = $this->map_post_props( $attachment, $map_att_type );
    $parent_post_id = $this->arg( $this->post_ids_map, $attachment->post_parent, 0 );
    // if ( $orig_parent_post_id and ! $parent_post_id ) { return; }
    $mapped_att_props->post_parent = $parent_post_id;
    // echo '<pre>import_attachment:props = ',
    //     print_r( $mapped_att_props, true ), '</pre>';
    // echo '<pre>import_attachment:attachment->post_id = ',
    //     print_r( $attachment->post_id, true ), '</pre>';
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
      // echo '<pre>import_attachment:attachment->post_parent = ',
      //   print_r( $attachment->post_parent, true ), '</pre>';
      // echo '<pre>import_attachment:post_ids_map = ',
      //   print_r( $this->post_ids_map, true ), '</pre>';
      $att_parent_post = ( $attachment->post_parent > 0 )
        ? $this->get_post_by_title( $attachment->post_parent_title,
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
      echo '<pre>INSERT attachment: ', print_r( $result, true ), '</pre>';
    }
    // UPDATE Attachment
    else /* $map_att_type == 'update' */
    {
      $mapped_att_props->ID = $existing_attachment->ID;
      $result = wp_update_post( $mapped_att_props, 'wp_error:true' );
      echo '<pre>UPDATE attachment: ', print_r( $result, true ), '</pre>';
    }
    if ( is_wp_error( $result ) )
    {
      $errors = $result->get_error_messages();
      foreach ( $errors as $error )
      {
        echo '<pre>import_attachment:error =', $error, '</pre>';
      }
      return;
    }

    $new_attachment_id = $result;

    /* Set attachment post metas */
    if ( $att_metas )
    {
      // echo '<pre>import_attachment:att_metas = ',
      //   print_r( $att_metas, true ), '</pre>';
      wp_update_attachment_metadata( $new_attachment_id, (array) $att_metas );
    }

    /* Copy the attachment file to uploads folder */
    if ( $file_path_rel )
    {
      $imports_path = $media_dir . '/' . $file_path_rel;
      $uploads_path = $this->uploads_info[ 'path' ] . '/' . $file_path_rel;
      // echo '<pre>import_attachment:imports_file_path = ',
      //   print_r( $imports_path, true ), '</pre>';
      // echo '<pre>import_attachment:uploads_file_path = ',
      //   print_r( $uploads_path, true ), '</pre>';
      if ( !file_exists( $uploads_path ) and file_exists( $imports_path ) )
      {
        wp_mkdir_p( dirname( $uploads_path ) ); // @return true == Dir exists
        echo @copy( $imports_path, $uploads_path )
          ? '<pre>Copy attachment ' . $new_attachment_id . ' file: OK</pre>'
          : '<pre>Copy attachment ' . $new_attachment_id . ' file: FAIL</pre>';
      }
      else
      {
        echo '<pre>Copy attachment ', $new_attachment_id, ' file: SKIP</pre>';
      }
    }

    $this->post_ids_map[ $attachment->post_id ] = $new_attachment_id;
    return $new_attachment_id;
  }


  function import_post( $post_props )
  {
    $loaded_data = $this->load_post_data( $post_props );
    $post_type = $loaded_data->props->post_type;
    $post_title = $loaded_data->props->post_title;
    $existing_post = $this->get_post_by_title( $post_title , $post_type );

    // echo '<pre>import_post:import_data = ',
    //   print_r( $loaded_data, true ), '</pre>';

    $orig_parent_thumbnail_id = null;
    $orig_parent_post_id = $loaded_data->props->post_id;
    $map_post_type = empty( $existing_post ) ? 'insert' : 'update';
    $mapped_props = $this->map_post_props( $loaded_data->props, $map_post_type );

    // echo '<pre>import_post:mapped_props = ',
    //   print_r( $mapped_props, true ), '</pre>';

    if ( isset( $mapped_props->thumbnail_id ) )
    {
      $orig_parent_thumbnail_id = $mapped_props->thumbnail_id;
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
      $result = wp_insert_post( $mapped_props, 'wp_error:true' );
      echo '<pre>INSERT POST: ', print_r( $result, true ), '</pre>';
      $post_id = $result;
    }
    else /* map_post_type == 'update' */
    {
      $post_id = $existing_post->ID;
      $mapped_props->ID = $post_id;
      $result = wp_update_post( $mapped_props, 'wp_error:true' );
      echo '<pre>UPDATE POST: ', print_r( $result, true ), '</pre>';
    }

    if ( is_wp_error( $result ) )
    {
      $errors = $result->get_error_messages();
      foreach ( $errors as $error ) {
        echo '<pre>import_post:error = ', $error, '</pre>';
      }
      return;
    }

    $this->post_ids_map[ $orig_parent_post_id ] = $post_id;

    if ( isset( $loaded_data->props->is_frontpage ) )
    {
      update_option( 'show_on_front', 'page', true );
      update_option( 'page_on_front', $post_id, true );
    }

    /* Assign Strategies */
    /* @TODO: What about other possibe taxonomies? */
    if ( $post_type == 'asset_manager' )
    {
      $terms = $loaded_data->props->terms?:[];
      $strategy_term_names = $this->extract_term_names( 'strategy', $terms );
      wp_set_post_terms( $post_id, $strategy_term_names, 'strategy', false );
    }

    $parent_post_id = $post_id;

    $index_attachments = array_merge(
      $loaded_data->props->post_attachments,
      $loaded_data->props->other_attachments
     );

    foreach ( $index_attachments as $attachment )
    {
      $new_attachment_id = $this->import_attachment( $attachment,
        $loaded_data->media_dir, $orig_parent_post_id );

      // echo '<pre>import_post:new_attachment_id = ',
      //   print_r( $new_attachment_id, true ), '</pre>';

      if ( ! $new_attachment_id ) { continue; }

      // Set the parent post thumbnail ID if this attachment is it's thumnail.
      $att_is_thumbnail = ( $attachment->post_id == $orig_parent_thumbnail_id );
      // echo '<pre>import_post_attachment:orig_parent_thumbnail_id = ',
      //   print_r( $orig_parent_thumbnail_id, true ), '</pre>';
      if ( $att_is_thumbnail )
      {
        update_post_meta( $parent_post_id, '_thumbnail_id', $new_attachment_id );
      }
    }
  }


  function import_all()
  {
    // echo '<pre>REQUEST: ', print_r( $_REQUEST, true ), '</pre>';
    check_admin_referer( 'fh_nonce' );

    echo '<pre>SITE URL: ', print_r( $this->site_url, true ), '</pre>';
    echo '<pre>THEME DIR: ', print_r( $this->theme_dir, true ), '</pre>';
    echo '<pre>IMPORT PATH: ', print_r( $this->import_path, true ), '</pre>';

    /* Uploads Info */
    $this->uploads_info = wp_get_upload_dir();
    $this->uploads_info[ 'uploads' ] = untrailingslashit( str_replace(
      trailingslashit( $this->site_url ), '', $this->uploads_info[ 'baseurl' ] ) );
    echo '<pre>UPLOADS INFO: ', print_r( $this->uploads_info, true ), '</pre>';

    /* Import Directory */
    $this->import_dir = $this->theme_dir . '/' . $this->import_path;
    echo '<pre>IMPORT DIR: ', print_r( $this->import_dir, true ), '</pre>';

    /* WP Options */
    $this->import_options();

    /* Taxonomies */
    $this->import_taxonomies();

    /* Unattached Media */
    $this->import_unattached_media();

    /* Posts */
    $this->import_posts();

    /* Nav Menu Items */
    $this->import_nav_menus();

    /* Theme Options */
    $this->import_theme_options();

    /* Widgets */
    $this->import_widgets();

    echo '<pre>POST IDS MAP: ', print_r( $this->post_ids_map, true ), '</pre>';
  }

} // end: FH_Import_Data


new FH_Import_Data( SITE_URL, THEME_DIR );


// ---

// echo '<pre>file_path: ', print_r( $file_path, true ), '</pre>';
// echo '<pre>relative_path: ', print_r( $relative_path, true ), '</pre>';
// $zip->addFile( $file_path, $relative_path );