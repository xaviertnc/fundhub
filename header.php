<?php
/**
 * The site header template file
 * 
 * Theme: FUNDHUB
 * Author: Neels Moller
 * Version: 1.0.0
 *
 */
?><!DOCTYPE html>
<html>

<head <?php language_attributes(); ?>>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<title><?=SITE_NAME?></title>
<!-- WP HEAD -->
<?php wp_head(); ?>
<!-- /WP HEAD -->
<!-- TRACKING & OTHER ADDITIONAL SCRIPTS -->
<?php echo get_option( 'fh_header_scripts' ), PHP_EOL; ?>
<!-- /TRACKING & OTHER ADDITIONAL SCRIPTS -->
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- TRACKING & OTHER NO-SCRIPT TAGS -->
<?php echo get_option( 'fh_noscripts' ), PHP_EOL; ?>
<!-- /TRACKING & OTHER NO-SCRIPT TAGS -->

<!-- Skip To Content Link -->
<a class="skip-link" href="#page-content">Skip to content</a>

<!-- SITE HEADER -->
<header id="site-header">

<!-- Header Container -->
<div class="container">

<!-- Site Brand -->
<div id="site-brand">
<span class="screen-reader-text">Welcome to <?=SITE_NAME?></span>
<small class="screen-reader-text"><?=TAGLINE?></small>
<?php if ( has_custom_logo() ) : the_custom_logo(); else : ?>
<a href="<?=SITE_URL?>/" rel="home" aria-current="page">
  <img src="<?=FH_ASSETS?>/img/fundhub/logo.png" alt="Company Logo" height="70">
</a><?php endif; ?>
</div>
<!-- /Site Brand -->

<!-- Primary Nav -->
<input id="toggle-nav-check" type="checkbox" aria-hidden="true">
<nav id="primary-nav">

<?php wp_nav_menu( array(
        
    'theme_location' => 'primary',
    'link_before' => '<span role="presentation">',
    'link_after' => '</span>'
        
) ); ?>


</nav>
<!-- /Primary Nav -->

<!-- Mobile Nav -->
<label id="toggle-nav-btn" for="toggle-nav-check" aria-label="Toggle Mobile Nav">
  <!-- &#9776; hamburger icon -->    
  <hr class="line1"><hr class="line2"><hr class="line3">
</label>
<!-- /Mobile Nav -->

</div>
<!-- /Header Container -->

</header>
<!-- /SITE HEADER -->
