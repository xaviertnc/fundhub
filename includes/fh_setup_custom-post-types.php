<?php

/*
 * Register Custom Post Types and Taxonomies
 */
function fh_setup_custom_posts()
{
  $labels = array(
     'name'              => _x( 'Strategies', 'taxonomy general name', 'fundhub' ),
     'singular_name'     => _x( 'Strategy', 'taxonomy singular name', 'fundhub' ),
     'search_items'      => __( 'Search Strategies', 'fundhub' ),
     'all_items'         => __( 'All Strategies', 'fundhub' ),
     'edit_item'         => __( 'Edit Strategy', 'fundhub' ),
     'update_item'       => __( 'Update Strategy', 'fundhub' ),
     'add_new_item'      => __( 'Add New Strategy', 'fundhub' ),
     'new_item_name'     => __( 'New Strategy Name', 'fundhub' ),
     'menu_name'         => __( 'Strategies', 'fundhub' ),
   );

   $args = array(
     'hierarchical'      => false,
     'labels'            => $labels,
     'show_ui'           => true,
     'show_in_rest'      => true,
     'show_admin_column' => true,
     'query_var'         => true,
     'public'            => true,
     'capability_type'   => 'post',
   );

   register_taxonomy( 'strategy', array( 'asset-manager' ), $args );
     
  $labels = array(
	'name' => __( 'Asset Managers', 'fundhub' ),
	'singular_name' => __( 'Asset Manager', 'fundhub' ),
	'add_new_item' => __( 'Add Asset Manager', 'fundhub' ),
	'edit_item' => __( 'Edit Asset Manager', 'fundhub' ),
	'view_item' => __( 'View Asset Manager', 'fundhub' ),
  );

  register_post_type( 'asset_manager', array(
	'label' => __( 'Asset Managers', 'fundhub' ),
	'labels' => $labels,
	'description' => '',
	'public' => true,
	'publicly_queryable' => true,
	'show_ui' => true,
	'show_in_rest' => true,
	'has_archive' => true,
	'show_in_menu' => true,
	'show_in_nav_menus' => true,
	'delete_with_user' => false,
	'exclude_from_search' => false,
	'capability_type' => 'post',
	'map_meta_cap' => true,
	'hierarchical' => false,
	'rewrite' => array( 'slug' => 'asset-manager' ),
	'query_var' => true,
	'menu_icon' => 'dashicons-businessperson',
	'menu_position' => 5,
	'supports' => array( 'title', 'editor', 'thumbnail', 'revisions', 
	   'author', 'custom-fields', 'page-attributes' ),
	'taxonomies' => array( 'strategy' ),
  ) );

}

add_action( 'init', 'fh_setup_custom_posts' );
