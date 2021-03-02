<?php

class FH_Lib {


  public static $delete_count = 0;


  static function quote_sql( $str )
  {
    return '\'' . esc_sql( $str ) . '\'';
  }


  static function delete_attachments( $post_id )
  {
    // Get all attachments IDs.
    $att_ids = get_posts( [
      'numberposts' => -1,
      'post_type'   => 'attachment',
      'fields'      => 'ids',
      'post_status' => 'any',
      'post_parent' => $post_id,
    ] );

    // Delete each attachments.
    if ( $att_ids && is_array( $att_ids ) )
    {
      foreach( $att_ids as $id )
      {
        wp_delete_attachment( $id, true );
      }
    }
  }


  static function find_attachment_id( $file_href )
  {
    global $wpdb;
    $sql = "SELECT post_id FROM $wpdb->postmeta
      WHERE meta_key = '_wp_attached_file' AND
        meta_value = '$file_href' LIMIT 1";
    return $wpdb->get_var( $sql );
  }


  static function find_object_by( $objects, $key, $value )
  {
    if ( ! is_array( $objects ) ) { return; }
    if ( ! isset( $objects[ 0 ]->$key ) ) { return; }
    foreach( $objects as $object )
    {
      if ( $object->$key == $value ) { return $object; }
    }
  }


  static function sort_objects_by( $objects, $sort_by, $numerical = false )
  {
    if ( $numerical )
    {
      usort( $objects, function( $a, $b ) use ( $sort_by ) {
        if ($a->$sort_by == $b->$sort_by) { return 0; }
        return ($a->$sort_by < $b->$sort_by) ? -1 : 1;
      } );
    }
    else
    {
      usort( $objects, function( $a, $b ) use ( $sort_by ) {
        return strcmp( $a->$sort_by, $b->$sort_by );
      } );
    }
    return $objects;
  }


  static function copy_online_file( $file_url, $dest_dir )
  {
    $filename = $dest_dir . DIRECTORY_SEPARATOR . basename( $file_url );
    $args_for_get = array( 'stream' => true, 'filename' => $filename );
    $response = wp_remote_get( $file_url, $args_for_get );
    return $response;
  }


  static function delete_dir( $dir, $base_dir )
  {
    $files = array_diff( scandir( $dir ), array( '.', '..' ) );
    foreach ( $files as $file )
    {
      if ( is_dir( $dir . DIRECTORY_SEPARATOR . $file ) )
      {
        FH_Lib::delete_dir( $dir . DIRECTORY_SEPARATOR . $file, $base_dir );
      }
      else
      {
        $result = @unlink( $dir . DIRECTORY_SEPARATOR . $file );
        FH_Lib::$delete_count += (int) $result;
      }
    }
    if ( $dir != $base_dir )
    {
      $result = @rmdir( $dir );
      FH_Lib::$delete_count += (int) $result;
      return $result;
    }
    return true;
  }


  static function upload_file( $file, $allowed_mime_types = [] )
  {
    include_once( ABSPATH . 'wp-admin/includes/file.php'  );
    include_once( ABSPATH . 'wp-admin/includes/media.php' );
    include_once( ABSPATH . 'wp-admin/includes/image.php' );

    if ( ! in_array( $file['type'], $allowed_mime_types ) ) {
      return new \WP_Error( 'upload',
        sprintf( __( 'Uploaded files need to be one of the following file types: %s', 
          'my-listing' ), implode( ', ', array_keys( $allowed_mime_types ) ) ) );
    }

    $upload = wp_handle_upload( $file, [ 'test_form' => false ] );

    if ( ! empty( $upload['error'] ) ) {
      return new \WP_Error( 'upload', $upload[ 'error' ] );
    }

    $wp_filetype = wp_check_filetype( $upload[ 'file' ] );
    $attach_id = wp_insert_attachment(
      [
        'post_mime_type' => $wp_filetype[ 'type' ],
        'post_title' => sanitize_file_name( $upload[ 'file' ] ),
        'post_content' => '',
        'post_status' => 'inherit'
      ],
      $upload[ 'file' ]
    );

    $attach_data = wp_generate_attachment_metadata( $attach_id, $upload[ 'file' ] );
    wp_update_attachment_metadata( $attach_id, $attach_data );

    return $attach_id;
  }

} // end: FH_Lib
