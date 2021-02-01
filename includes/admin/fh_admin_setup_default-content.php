<?php  /* FUND HUB Theme Default Content Functions */


function fh_default_content( $content, $post )
{
  switch( $post->post_type ) {
    case 'asset_manager':
      $args = array(
        'name'        => 'asset-manager-template',
        'post_status' => array( 'private' ),
        'post_type' => array( 'page' ),
        'numberposts' => 1
      );
      $posts = get_posts( $args );
      if( $posts )
      {
        $post = reset( $posts );
        $content = $post->post_content;
      }
      else
      {
        $content = '<!-- wp:paragraph --><p>No template found :/</p><!-- /wp:paragraph -->';
      }
      break;
    case 'post':
      $content = '';
      break;
    case 'page':
      $content = '';
      break;
    default:
      $content = '';
      break;
  }
  return $content;
}

add_filter( 'default_content', 'fh_default_content', 10, 2 );