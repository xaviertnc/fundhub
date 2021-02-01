<?php

function fh_export_terms( $terms )
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


function fh_export_menu_items( $menu_items )
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


function fh_export_attachments( $attachments )
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


function fh_export_page( $page )
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
  $export_item->post_content_files = $page->post_content_files;
  $export_item->post_attachments = $page->post_attachments;
  $export_item->other_attachments = $page->other_attachments;
  $export_item->menu_order = $page->menu_order;
  // $export_item->filter = $page->filter;
  return $export_item;
}


/**
 * Cycle through each file HREF found inside $post_content
 * and determine if it's a local or external file href.
 * Also check if the href points inside the WP media library
 * and find its matching attachment post and ID if possible.
 *
 * PS: Duplicate HREFS are removed before processing.
 */
function fh_get_post_content_files( $post_content, $post_attachments )
{
  $results = array();
  $regex = '/(?:src|href)="([^"]*\.(?:jpg|png|gif|pdf))"/i';
  preg_match_all( $regex, $post_content, $matches );
  if ( ! empty( $matches[1] ) )
  {
    $raw_file_hrefs = array();
    // Get raw hrefs while preventing duplicates
    foreach ( $matches[1] as $raw_file_href )
    {
      if ( !  in_array( $raw_file_href, $raw_file_hrefs ) )
      {
        $raw_file_hrefs[] = $raw_file_href;
      }
    }
    // Process unique hrefs and add them to $results
    foreach ( $raw_file_hrefs as $raw_file_href )
    {
      //echo '<pre>raw_file_href: ', print_r( $raw_file_href, true ), '</pre>';
      $pos_http = strpos( $raw_file_href, 'http' );
      //echo '<pre>SITE_URL: ', print_r( SITE_URL, true ), '</pre>';
      //echo '<pre>pos_http: ', print_r( $pos_http, true ), '</pre>';
      $pos_siteurl = strpos( $raw_file_href, SITE_URL );
      //echo '<pre>pos_siteurl: ', print_r( $pos_siteurl, true ), '</pre>';
      $is_external = ( ( $pos_http !== FALSE ) and ( $pos_siteurl === FALSE ) );
      //echo '<pre>is_external: ', print_r( $is_external, true ), '</pre>';
      $file_href = $raw_file_href;
      $wp_post_attachment = null;
      $wp_attachment_id = null;
      $is_media_item = false;
      if ( ! $is_external )
      {
        $uploads_path = $pos_http === FALSE ? UPLOADS : SITE_URL .'/' . UPLOADS;
        if ( $uploads_path ) { $uploads_path .= '/'; }
        //echo '<pre>uploads_path: ', print_r( $uploads_path, true ), '</pre>';
        $is_media_item = ( UPLOADS and strpos( $raw_file_href, $uploads_path ) !== FALSE );
        if ( $is_media_item )
        {
          $file_href = str_replace( $uploads_path, '', $raw_file_href );
          //echo '<pre>file_href: ', print_r( $file_href, true ), '</pre>';
          $wp_attachment_id = fh_get_attachment_id( $file_href );
          $wp_post_attachment = fh_find_object_by( $post_attachments,
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


function fh_get_other_media( $post_content_files = null )
{
  $attachments = array();
  $shared_attachment_ids = array();
  echo '<pre>post_content_files: ', print_r( $post_content_files, true ), '</pre>';
  if ( empty( $post_content_files ) ) { return $attachments; }
  foreach ( $post_content_files as $file )
  {
    if ( $file->is_media_item and ! $file->is_attached_to_post )
    {
      $shared_attachment_ids[] = $file->wp_attachment_id;
    }
  }
  //echo '<pre>shared_attachment_ids: ', print_r( $shared_attachment_ids, true ), '</pre>';
  if ( $shared_attachment_ids )
  {
    $args = array(
      'post_type' => 'attachment',
      'post__in' => $shared_attachment_ids
    );
    $attachments = get_posts( $args );
    //echo '<pre>attachments: ', print_r( $attachments, true ), '</pre>';
  }
  return $attachments;
}


function fh_action_export()
{
  check_admin_referer( 'fh-nonce' );

  //echo '<pre>REQUEST: ', print_r( $_REQUEST, true ), '</pre>';
  echo '<pre>SITE_URL: ', print_r( SITE_URL, true ), '</pre>';
  echo '<pre>UPLOADS: ', print_r( UPLOADS, true ), '</pre>';

  $export_basedir = THEME_DIR . '/export';

  // Get categories
  $category_terms = fh_get_taxonomy_terms( 'category' );
  // Prepare categories for export
  $category_terms = fh_export_terms( $category_terms );
  // Sort categories alphabetically
  $category_terms = fh_sort_objects_by( $category_terms, 'name' );
  // Create "Categories" folder
  $categories_basedir = $export_basedir;
  if ( fh_create_folder( $categories_basedir ) )
  {
    fh_save_as_json( $categories_basedir . '/categories.json', $category_terms );
    //echo '<pre>Categories: ', print_r( $category_terms, true ), '</pre>';
  }

  // Get strategies
  $strategy_terms = fh_get_taxonomy_terms( 'strategy' );
  // Prepare strategies for export
  $strategy_terms = fh_export_terms( $strategy_terms );
  // Sort categories alphabetically
  $strategy_terms = fh_sort_objects_by( $strategy_terms, 'name' );
  // Create "Categories" folder
  $strategies_basedir = $export_basedir;
  if ( fh_create_folder( $strategies_basedir ) )
  {
    fh_save_as_json( $strategies_basedir . '/strategies.json', $strategy_terms );
    //echo '<pre>Strategies: ', print_r( $strategy_terms, true ), '</pre>';
  }

  // Get Nav Menus
  $nav_menus = array();
  $nav_menu_terms = fh_get_taxonomy_terms( 'nav_menu' );
  // Sort Menu Terms by Name
  $nav_menu_terms = fh_sort_objects_by( $nav_menu_terms, 'name' );
  // Get Nav Menu Item Posts
  foreach ( $nav_menu_terms as $nav_menu_term )
  {
    $nav_menu_items = wp_get_nav_menu_items( $nav_menu_term );
    $nav_menu_items = fh_export_menu_items( $nav_menu_items );
    $nav_menus[ $nav_menu_term->name ] = $nav_menu_items;
  }
  $nav_menus_basedir = $export_basedir;
  if ( fh_create_folder( $nav_menus_basedir ) )
  {
    fh_save_as_json( $nav_menus_basedir . '/navmenus.json', $nav_menus );
    // echo '<pre>Nav Menus: ', print_r( $nav_menus, true ), '</pre>';
  }

  // Get Pages
  $pages_basedir = "{$export_basedir}/pages";
  $pages = fh_get_post_type( 'page' );
  if ( $pages and fh_create_folder( $pages_basedir ) )
  {
    $pages = fh_sort_objects_by( $pages, 'menu_order', 'numeric' );
    foreach ( $pages as $page )
    {
      $page_basedir = "{$pages_basedir}/{$page->post_name}";
      if ( fh_create_folder( $page_basedir ) )
      {
        fh_save_as_html( $page_basedir . '/content.html', $page->post_content );
        $page->post_attachments = fh_get_attached_media( $page );
        $page->post_attachments = fh_add_post_metas( $page->post_attachments );
        $page->post_attachments = fh_export_attachments( $page->post_attachments );
        $page->post_content_files = fh_get_post_content_files( $page->post_content, $page->post_attachments );
        $page->other_attachments = fh_get_other_media( $page->post_content_files );
        $page->other_attachments = fh_add_post_metas( $page->other_attachments );
        $page->other_attachments = fh_export_attachments( $page->other_attachments );
        $page->post_metas = get_post_meta( $page->ID );
        $page->post_metas = fh_filter_post_metas( $page->post_metas );
        $page = fh_export_page( $page );
        fh_save_as_json( $page_basedir . '/post.json', $page );
        echo '<pre>' . $page->post_title . ': ', print_r( $page, true ), '</pre>';
        $uploads_info = wp_upload_dir();
        $uploads_baseurl = $uploads_info[ 'baseurl' ];
        //echo '<pre>uploads_baseurl: ', print_r( $uploads_baseurl, true ), '</pre>';
        // $uploads_basedir = $uploads_info[ 'basedir' ];
        $files_basedir = $page_basedir . '/media';
        //echo '<pre>files_basedir: ', print_r( $files_basedir, true ), '</pre>';
        if ( fh_create_folder( $files_basedir ) )
        {
          if ( $page->post_attachments  )
          {
            foreach ( $page->post_attachments as $attachment )
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
          if ( $page->other_attachments  )
          {
            foreach ( $page->other_attachments as $attachment )
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

  // Get Asset Managers
  $asm_posts_basedir = "{$export_basedir}/asset-manager-posts";
  $asm_posts = fh_get_post_type( 'asset_manager' );
  if ( $asm_posts and fh_create_folder( $asm_posts_basedir ) )
  {
    $asm_posts = fh_sort_objects_by( $asm_posts, 'menu_order', 'numeric' );
    foreach ( $asm_posts as $asm )
    {
      $asm_basedir = "{$asm_posts_basedir}/{$asm->post_name}";
      if ( fh_create_folder( $asm_basedir ) )
      {
        fh_save_as_html( $asm_basedir . '/content.html', $asm->post_content );
        $asm->post_attachments = fh_get_attached_media( $asm );
        $asm->post_attachments = fh_add_post_metas( $asm->post_attachments );
        $asm->post_attachments = fh_export_attachments( $asm->post_attachments );
        $asm->post_content_files = fh_get_post_content_files( $asm->post_content, $asm->post_attachments );
        $asm->other_attachments = fh_get_other_media( $asm->post_content_files );
        $asm->other_attachments = fh_add_post_metas( $asm->other_attachments );
        $asm->other_attachments = fh_export_attachments( $asm->other_attachments );
        $asm->post_metas = get_post_meta( $asm->ID );
        $asm->post_metas = fh_filter_post_metas( $asm->post_metas );
        $asm = fh_export_page( $asm );
        fh_save_as_json( $asm_basedir . '/post.json', $asm );
        echo '<pre>' . $asm->post_title . ': ', print_r( $asm, true ), '</pre>';
        $uploads_info = wp_upload_dir();
        $uploads_baseurl = $uploads_info[ 'baseurl' ];
        //echo '<pre>uploads_baseurl: ', print_r( $uploads_baseurl, true ), '</pre>';
        // $uploads_basedir = $uploads_info[ 'basedir' ];
        $files_basedir = $asm_basedir . '/media';
        //echo '<pre>files_basedir: ', print_r( $files_basedir, true ), '</pre>';
        if ( fh_create_folder( $files_basedir ) )
        {
          if ( $asm->post_attachments  )
          {
            foreach ( $asm->post_attachments as $attachment )
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
          if ( $asm->other_attachments  )
          {
            foreach ( $asm->other_attachments as $attachment )
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

//   $zip = new ZipArchive;
//   $zip->open( THEME_DIR . '/export.zip', ZipArchive::CREATE );
//   $files_to_zip = array();
//   $zip->addFile( THEME_DIR . '/export' );
//   $zip->close();

}

add_action( 'admin_post_fh_export', 'fh_action_export' );