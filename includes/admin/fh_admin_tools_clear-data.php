<?php

function fh_action_clear_data()
{
	global $current_user, $wpdb;

  check_admin_referer( 'fh_nonce' );

  // only admins can reset; double-check
  if (!current_user_can('administrator')) {
    return false;
  }

  wp_redirect(admin_url() . '?clear-data=success');

  exit;

}

add_action( 'admin_post_fh_clear_data', 'fh_action_clear_data' );