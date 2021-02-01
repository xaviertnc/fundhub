<?php
/**
 * @package FUNDHUB
 * @subpackage Templates
 * @version 1.0.0
 */

get_header(); ?>

<!-- SITE CONTENT -->
<section id="site-content">

<?php get_template_part( 'templates/part/post-header' ); ?>

<!-- Page Content -->
<main id="page-content" role="main">

<!-- content container -->
<div class="container clr" role="presentation">

<?php get_template_part( 'templates/part/post-content' ); ?>

</div>
<!-- /content container -->

</main>
<!-- /Page Content -->
    
</section>
<!-- /SITE CONTENT -->

<?php get_footer(); ?>