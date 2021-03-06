<?php
/**
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
<div class="container clr" role="presentation">

<!-- content left -->
<div class="content-left" role="presentation">

<?php get_template_part( 'templates/part/page-content' ); ?>

</div>
<!-- /content left -->

<!-- content right -->
<?php dynamic_sidebar('sidebar-main'); ?>
<!-- /content right -->

</div>
<!-- /content container -->

</main>
<!-- /Page Content -->

<?php // get_template_part( 'templates/part/page-footer' ); ?>

</section>
<!-- /SITE CONTENT -->

<?php get_footer(); ?>