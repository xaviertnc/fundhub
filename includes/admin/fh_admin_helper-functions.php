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
