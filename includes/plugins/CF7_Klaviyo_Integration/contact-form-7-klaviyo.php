<?php
/*
Plugin Name: Contact Form 7 - Klaviyo Integration
Plugin URI: http://www.maxroi.co.za
Description: Integrates Contact Form 7 with Klaviyo allowing form submissions to be automatically sent to your Klaviyo account.
Version: 1.0
Author: MaxROI
Author URI: http://www.maxroi.co.za
*/

register_activation_hook( __FILE__, 'cf7_klaviyo_activate' );
register_deactivation_hook( __FILE__, 'cf7_klaviyo_deactivate' );
register_uninstall_hook( __FILE__, 'cf7_klaviyo_uninstall' );


function cf7_klaviyo_activate()
{
  add_option( 'cf7_klaviyo', array() );
}


function cf7_klaviyo_deactivate()
{
    return;
}


function cf7_klaviyo_uninstall()
{
  delete_option( 'cf7_klaviyo' );
}


// check to make sure contact form 7 is installed and active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) )
{
   include 'includes/class-wp-klaviyo.php'; 
   include 'includes/class-klaviyo-cf7-integration.php';
   $cf7_klaviyo = Klaviyo_CF7_Integration::get_instance();
   add_action( 'init', array( $cf7_klaviyo, 'init' ) );
}