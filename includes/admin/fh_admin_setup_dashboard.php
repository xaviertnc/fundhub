<?php  /* FUND HUB Setup Admin Dashboard */


function fh_dashboard_setup() {
	global $wp_meta_boxes;
    // https://digwp.com/2014/02/disable-default-dashboard-widgets/
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
}

add_action('wp_dashboard_setup', 'fh_dashboard_setup', 999);
