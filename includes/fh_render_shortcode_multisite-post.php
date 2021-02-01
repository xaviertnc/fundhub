<?php


function fh_render_multisite_post( $atts, $default_content = null )
{
  /**
   * Render any multisite post into another post to enabling us to have only one
   * Master Post in a network of sites sharing the same post content.
   */
  $attributes = shortcode_atts(
    array(
      'blog' => 1,
      'post' => 0
    ),
    $atts
  );
  $content = null;
  if ( $attributes['blog'] and $attributes['post'] )
  {
    switch_to_blog( $attributes['blog'] );
    $post = get_post( $attributes['post'] );
    if ( $post )
    {
      $content = get_the_content( null, null, $post );
      if ( $content )
      {
        $content = apply_filters( 'the_content', $content );
      }
    }
    restore_current_blog();
  }
  return $content ?: $default_content;
}

add_shortcode('multisite-post', 'fh_render_multisite_post');