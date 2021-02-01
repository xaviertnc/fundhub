<?php
/**
 * Template Name: Full Width
 * Template Post Type: post, page
 * 
 * @package FUNDHUB
 * @subpackage Templates
 * @version 1.0.0
 */

get_header(); ?>

<!-- SITE CONTENT -->
<section id="site-content">

<?php get_template_part( 'templates/part/page-header' ); ?>

<!-- Page Content -->
<main id="page-content" role="main">

<!-- content container -->
<div class="container clr">

<?php get_template_part( is_single()
   ? 'templates/part/post-content'
   : 'templates/part/page-content' );
?>

</div>
<!-- /content container -->

</main>
<!-- /Page Content -->

<?php // get_template_part( 'templates/part/page-footer' ); ?>
    
</section>
<!-- /SITE CONTENT -->

<?php get_footer(); ?>