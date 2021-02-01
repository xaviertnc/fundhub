<?php
  if( have_posts() ) { the_post(); } else { die( 'Oops, no post found!' ); }
  $header_title = get_post_meta( $post->ID, 'Header Title', true );
  $header_subtitle = get_post_meta( $post->ID, 'Header Subtitle', true );
?>
<!-- Page Header -->
<header id="page-header">
  <div class="container" role="presentation">
    <div class="loading-indicator"></div>
    <!-- page title -->
    <h1 class="fade-in-scale"><?=$header_title?:$post->post_title?></h1>
<?php if( $header_subtitle ): ?>
    <h2 class="fade-in-scale"><?=$header_subtitle?></h2>
<?php endif; ?>
    <hr class="accent-line">
    <!-- /page title -->
    <!-- header widgets -->

<?php dynamic_sidebar('sidebar-header'); ?>

    <!-- /header widgets -->
  </div>
</header>
<!-- /Page Header -->
