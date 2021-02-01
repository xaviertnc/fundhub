<?php
/**
 * @package FUNDHUB
 * @subpackage Templates
 * @version 1.0.0
 */
get_header(); ?>

<!-- SITE CONTENT -->
<section id="site-content">

<!-- Page Header -->
<header id="page-header"></header>
<!-- /Page Header -->

<!-- Page Content -->
<main id="page-content">

<!-- content container -->
<div class="container clr">

<style>

    #error {
      position: relative;
      height: 100vh;
    }

    #error .error {
      position: absolute;
      left: 50%;
      top: 30%;
      -webkit-transform: translate(-50%, -50%);
          -ms-transform: translate(-50%, -50%);
              transform: translate(-50%, -50%);
    }

    .error {
      max-width: 767px;
      width: 100%;
      line-height: 1.4;
      padding: 0px 15px;
    }

    .error .error-404 {
      position: relative;
      height: 150px;
      line-height: 150px;
      margin-bottom: 25px;
    }

    .error .error-404 h1 {
      color: gainsboro;
      font-family: 'TeXGyreAdventor-Regular', sans-serif;
      font-size: 10rem;
      font-weight: bold;
      margin: 0px;
      text-transform: uppercase;
    }

    .error h2 {
      font-size: 26px;
      font-weight: 700;
      margin: 0;
      color: white;
    }

    .error p {
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 0px;
      text-transform: uppercase;
    }

    .error a {
      font-family: Arial, Helvetica, sans-serif;
      display: inline-block;
      text-transform: uppercase;
      color: #fff;
      text-decoration: none;
      border: none;
      background: #5c91fe;
      padding: 10px 40px;
      font-size: 14px;
      font-weight: 700;
      border-radius: 1px;
      margin-top: 15px;
      -webkit-transition: 0.2s all;
      transition: 0.2s all;
    }

    .error a:hover {
      opacity: 0.8;
    }

    @media only screen and (max-width: 767px) {
      .error .error-404 {
        height: 110px;
        line-height: 110px;
      }
      .error .error-404 h1 {
        font-size: 7rem;
      }
    }
</style>

<div id="error">
	<div class="error">
		<div class="error-404">
			<h1>404</h1>
		</div>
		<h2>Oops! This Page Could Not Be Found</h2>
		<p>Sorry but the page you are looking for does not exist, have been removed. name changed or is temporarily unavailable</p>
		<a href="<?=SITE_URL?>">Go To Homepage</a>
	</div>
</div>
<!-- /error -->

</div>
<!-- /content container -->

</main>
<!-- /Page Content -->

</section>
<!-- /SITE CONTENT -->

<?php get_footer(); ?>
