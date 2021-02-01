<?php
/**
 * The site footer template file
 * 
 * Theme: FUNDHUB
 * Author: Neels Moller
 * Version: 1.0.0
 *
 */
?>
<!-- SITE FOOTER -->
<footer id="site-footer">

<!-- Site Footer Widgets -->
<?php dynamic_sidebar('sidebar-site-footer'); ?>

<!-- /Site Footer Widgets -->    

<!-- Site Footer White Section -->
<section class="site-footer-white">
    
<!-- Site Footer Content Container -->
<div class="container" role="presentation">

<!-- Site Footer Content Left -->
<p class="footer-content-left">&copy; Copyright - BLACK ONYX 2020</p>
<!-- /Site Footer Content Left -->

<!-- Secondary Nav -->
<nav id="secondary-nav">

<?php wp_nav_menu( array(
        
    'theme_location' => 'secondary',
    'link_before' => '<span role="presentation">',
    'link_after' => '</span>'
        
) ); ?>


</nav>
<!-- /Secondary Nav -->

</div>
<!-- /Site Footer Content Container -->

</section>
<!-- /Site Footer White Section -->

</footer>
<!-- /SITE FOOTER -->


<!-- Back To Top Link -->
<a href="#site-header" id="back-to-top" onclick="window.scrollTo(0,0);">
  <i class="arrow-up"></i>
  <span class="screen-reader-text">Back To Top</span>
</a>


<!-- WP FOOTER -->
<?php

wp_footer(); ?>

<!-- /WP FOOTER -->


<!-- TRACKING & OTHER ADDITIONAL FOOTER SCRIPTS -->
<?php echo get_option( 'fh_footer_scripts' ), PHP_EOL; ?>
<!-- /TRACKING & OTHER ADDITIONAL FOOTER SCRIPTS -->

</body>
</html>