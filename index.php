<?php
/**
 * @package FUNDHUB
 * @subpackage Templates
 * @version 1.0.0
 */

get_header();?>

<!-- SITE CONTENT -->
<section id="site-content">

<!-- Page Header -->
<header id="page-header">
</header>
<!-- /Page Header -->

<!-- Page Content -->
<main id="page-content">

<!-- container -->
<div class="container clr">
<?php $i = 0; while ( have_posts() ): ?>
<article>

<?php the_post(); the_content(); $i++; ?>

</article>
<?php endwhile; ?>
<?php if ( $i == 0 ): ?>

  <p>* Index - No Content *</p>

<?php endif; ?>
</div>
<!-- /container -->

</main>
<!-- /Page Content -->

</section>
<!-- /SITE-CONTENT -->

<?php get_footer();