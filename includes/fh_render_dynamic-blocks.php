<?php

/* Render Gutenberg Dynamic Block Content */

function fh_render_asm_strategies_tagcloud()
{
  global $post;
  $strategies = wp_get_post_terms( $post->ID , 'strategy');
  $html = '<ul class="post-strategies-block">';
  foreach( $strategies ?: [] as $strategy )
  {
    $html .= '<li><a href="strategies/#' . $strategy->name . '">' . 
      $strategy->name . '</a></li>';
  }
  return $html . '</ul>' . PHP_EOL;
}

register_block_type( 'fundhub/asm-strategies-tagcloud',
  array( 'render_callback' => 'fh_render_asm_strategies_tagcloud' )
);


function fh_render_strategies_page_content()
{
  $strategies = get_terms( 'strategy', array( 'hide_empty' => false ) );
  $html = '<ul class="strategies-block">';
  foreach( $strategies ?: [] as $strategy )
  {
    $html .= '<li><a href="#' . $strategy->name . '">' . 
      $strategy->name . '</a></li>';
  }
  if( ! $strategies ) { $html .= '<li>...</li>'; }
  $html .= '</ul>' . PHP_EOL;
  foreach( $strategies ?: [] as $strategy )
  {
    $description = !empty( $strategy->description ) ? $strategy->description
      : 'Contact the listed asset manager(s) for more information.';
    $args = array (
      'posts_per_page'  => 100,
      'orderby' => array( 'menu_order' => 'ASC', 'post_title' => 'ASC' ),
      'post_type' => 'asset_manager',
      'tax_query' => array(
         array(
          'taxonomy' => 'strategy',
          'terms' => $strategy->term_id,
          'field' => 'term_id'
         ),
      ),  
    );
    $post_tags_html = '';
    $posts = get_posts( $args );
    foreach ( $posts as $post )
    {
      $post_tags_html .= '<li class="grid-item"><a href="' . 
        esc_url( get_permalink( $post ) ) . '" title="' . $post->post_title . 
          '">' . get_the_post_thumbnail( $post->ID, 'full' ) . '</a></li>';
    }
    $html .= '<div class="strategy-block">' .
      '<a class="anchor" id="' . $strategy->name . '"></a>' .
      '<dt>' . $strategy->name . '</dt>' .
      '<dd>' . $description . '</dd>' .
      '<h5>Managers with this strategy.</h5>' .
      '<ul>' . $post_tags_html . '</ul>' .
    '</div>' . PHP_EOL;
  }
  return $html;
}

register_block_type( 'fundhub/strategies-page',
  array( 'render_callback' => 'fh_render_strategies_page_content' )
);