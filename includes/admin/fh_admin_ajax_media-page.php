<?php


function fh_ajax_get_media()
{
  $result = new stdClass();
  $dir_info = wp_get_upload_dir();
  $result->uploads_url = $dir_info['url'];
  $result->media_admin_url = admin_url( 'upload.php' );
  // $result->posts = array();
  $result->links = array();
  $args = array(
    'post_type' => 'attachment',
    'numberposts' => -1,
    'post_status' => null,
    'post_parent' => null, // any parent
  ); 
  $attachments = get_posts($args);
  if ($attachments) {
    foreach ($attachments as $post) {
      // setup_postdata($post);
      // the_title();
      // $result->posts[] = $post;
      $result->links[] = wp_get_attachment_link( $post->ID, false );
      // the_excerpt();
    }
  }
  if( ! empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] )
    && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' )
  {
    $result = json_encode( $result );
    echo $result;
  }
  die;
}

add_action( 'wp_ajax_fh_get_media', 'fh_ajax_get_media' );