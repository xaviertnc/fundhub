<?php


function fh_manage_posts_columns( $columns )
{
  global $post;
  if ( empty ( $post ) or $post->post_type != 'asset_manager' )
  {
    return $columns;
  }
  $n_columns = [];
  foreach ( $columns as $col_name => $col_title ) {
    $n_columns[$col_name] = $col_title;
    // if ( $col_name == 'cb' ) { $n_columns['id'] = 'ID'; }
    if ( $col_name == 'title' ) { $n_columns['logo'] = 'Featured Image'; }
  }
  $n_columns['menu_order'] = 'Ord';
  $n_columns['id'] = 'ID';
  return $n_columns;
}

add_filter('manage_posts_columns', 'fh_manage_posts_columns', 2);


function fh_render_posts_custom_column( $column_name, $post_id )
{
  global $post;
  if ( $post->post_type != 'asset_manager' ) { return; }
  switch ( $column_name )
  {
    case 'id': echo $post->ID; break;
	case 'logo' : echo the_post_thumbnail(); break;
    case 'menu_order': echo $post->menu_order; break;
  }
}

add_action('manage_posts_custom_column', 'fh_render_posts_custom_column', 5, 2);


function fh_manage_sortable_columns( $columns ) {
  $columns['menu_order'] = 'menu_order';
  return $columns;
}

add_filter( 'manage_edit-post_sortable_columns', 'fh_manage_sortable_columns' );


function pre_get_posts( $query )
{
  if ($query->get( 'post_type' ) == 'asset_manager') {
    if ($query->get( 'orderby' ) == '') {
      $query->set( 'orderby', array(
        'menu_order' => 'ASC' ,
        'post_title' => 'ASC'
      ) );
    }
  }
}

add_action( 'pre_get_posts', 'pre_get_posts', 9 );


function fh_enable_post_features()
{
  add_post_type_support( 'post', 'page-attributes' );
//   $post_type = get_post_type_object('post');
//   $post_type->hierarchical = true;
}

add_action( 'admin_init', 'fh_enable_post_features' );