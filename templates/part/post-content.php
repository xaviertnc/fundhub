<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <!-- Post Header -->
  <header class="post-header">
    <div class="content-left" role="presentation">
      <?php the_post_thumbnail( 'full', ['class' => 'img-featured' ] ); ?>
    </div>
    <div class="content-right clr" role="presentation">
      <div class="cta-goto-website pull-right">
        <label>Asset Manager's Website</label>
        <a href="<?=get_post_meta( $post->ID, 'Website', true )?>" class="link-button">
          Visit <?=$post->post_title?>
        </a>
      </div>
    </div>
  </header>
  <!-- /Post Header -->
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