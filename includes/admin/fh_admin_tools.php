<?php


function fh_quote_sql( $str )
{
  return '\'' . esc_sql( $str ) . '\'';
}


function fh_copy_file( $file_url, $dest_dir )
{
  $filename = $dest_dir . '/' . basename( $file_url );
  $args_for_get = array( 'stream' => true, 'filename' => $filename );
  $response = wp_remote_get( $file_url, $args_for_get );
  return $response;
}


function fh_save_as_json( $file_path, $data )
{
  return file_put_contents( $file_path,
    json_encode( $data, JSON_PRETTY_PRINT ) );
}


function fh_save_as_html( $file_path, $html )
{
  return file_put_contents( $file_path, $html );
}


function fh_create_folder( $theme_relative_path )
{
  return wp_mkdir_p( $theme_relative_path );
}


function fh_sort_objects_by( $objects, $sort_by, $numerical = false )
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


function fh_find_object_by( $objects, $key, $value )
{
  if ( ! is_array( $objects ) ) { return; }
  if ( ! isset( $objects[ 0 ]->$key ) ) { return; }
  foreach( $objects as $object )
  {
    if ( $object->$key == $value ) { return $object; }
  }
}


function fh_get_taxonomy_terms( $taxonomy )
{
  $terms = get_terms( array(
    'taxonomy' => $taxonomy,
    'hide_empty' => false,
  ) );
  return $terms;
}


function fh_get_post_type( $post_type = 'post', $status = null, $parent = null, $limit = 999 )
{
  $args = array(
    'post_type'   => $post_type,
    'post_status' => $status, // null => any status
    'post_parent' => $parent, // null => any parent
    'numberposts' => $limit,
  ); 
  $posts = get_posts( $args );   
  return $posts;
}


function fh_add_post_metas( $posts )
{
  foreach ( $posts as $post )
  {
    $post->post_metas = get_post_meta( $post->ID );
    unset( $post->post_metas[ '_wp_attachment_metadata' ] );
  }
  return $posts;
}


function fh_filter_post_metas( $post_metas )
{
  return array_filter( $post_metas, function( $key ) {
    return $key == '_thumbnail_id' or strpos( $key, '_' ) !== 0;
  }, ARRAY_FILTER_USE_KEY );
}


function fh_get_attached_media( $post )
{
  return get_attached_media( '', $post->ID );
}


function fh_get_attachment_id( $file_href )
{
  global $wpdb;
  $sql = "SELECT `post_id` FROM {$wpdb->postmeta}
    WHERE `meta_key` = '_wp_attached_file' AND 
      `meta_value` = '{$file_href}'";
  //echo '<pre>sql: ', print_r( $sql, true ), '</pre>';  
  $results = $wpdb->get_results( $sql );
  //echo '<pre>results: ', print_r( $results, true ), '</pre>';  
  foreach ( $results?:[] as $result )
  {
    return $result->post_id;
  }
}