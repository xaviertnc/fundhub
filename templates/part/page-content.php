<article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
<?php
  $more_link_text = null; $strip_teaser = null;
  $content = get_the_content( $more_link_text, $strip_teaser );
  if ( $content )
  {
    $content = apply_filters( 'the_content', $content );
    $content = str_replace( ']]>', ']]&gt;', $content );
  }
  else { $content = '<p>* No Content *</p>';  }
  echo $content;
?>
</article>