<?php
/**
 * fh-widget-logos-grid.php
 *
 * Fund Hub Logos Grid Widget
 *
 * Theme: FUNDHUB
 * Author: Neels Moller
 * Version 1.0.0
 *
 */
class FH_Logos_Grid extends WP_Widget {

  public function __construct()
  {
    parent::__construct(

      // Base ID of your widget
      'fh_logos_grid',

      // Widget name will appear in UI
      __('FundHub: Logos Grid', 'fundhub'),

      array( 'description' =>
        __( 'Display post-type featured images in a grid', 'fundhub' ) )
    );
  }


  // Widget Frontend - Render Grid
  public function render_logos_grid( $post_type = 'post' )
  {
    $posts = get_posts( array(
      'post_status' => 'publish',
      'orderby' => array( 'menu_order' => 'ASC', 'post_title' => 'ASC' ),
      'post_type' => $post_type,
      'posts_per_page'  => -1
    ) );
    if( $posts ) {
      foreach( $posts as $post )
      {
        $title = esc_attr( $post->post_title );
        $thumbnail_html = get_the_post_thumbnail( $post->ID );
        if ( $thumbnail_html )
        {
          echo '<div class="grid-item"><a href="',
            esc_url( get_permalink( $post ) ), '" title="', $title, '">',
              $thumbnail_html, '</a></div>';
        }
        else
        {
          echo '<div class="grid-item"><a href="',
            esc_url( get_permalink( $post ) ), '" title="', $title, '">',
              $title, '</a></div>';
        }
      }
    }
    else
    {
      echo '      <p style="text-align:center">LOGOS GRID<br>',
        '* Empty *</p>', PHP_EOL;
    }
  }


  // Widget Frontend - Render Widget
  public function widget( $args, $instance )
  {
    $classes = empty( $instance['classes'] )
      ? '' : ' ' . esc_attr( $instance['classes'] );
    echo $args['before_widget'];
    echo '    <!-- fundhub logos grid -->' . PHP_EOL;
    if ( isset( $instance[ 'title' ] ) )
    {
       echo '    <div class="cta-click-on-logos">' .
         wp_kses( $instance[ 'title' ], array( 'br' => array() ) ) .
         '</div>' . PHP_EOL;
    }
?>
    <div class="fh-logos-grid<?=$classes?> framed">
<?php $this->render_logos_grid( $instance['post_type'] ); ?>
    </div>
    <!-- /fundhub logos grid -->
<?php
    echo $args['after_widget'];
  }


  // Widget Backend - Render settings form
  public function form( $instance )
  {
    if( isset( $instance[ 'title' ] ) )
    {
      $title = $instance[ 'title' ];
    }
    else
    {
      $title = '';
    }
    if( isset( $instance[ 'post_type' ] ) )
    {
      $post_type = $instance[ 'post_type' ];
    }
    else
    {
      $post_type = 'post';
    }
    if( isset( $instance[ 'classes' ] ) )
    {
      $classes = $instance[ 'classes' ];
    }
    else
    {
      $classes = '';
    }
    $post_types = get_post_types( array( 'public' => true ) );
?>
<p>
  <label for="<?=$this->get_field_id( 'title' )?>">Callout Text</label>
  <textarea class="widefat" id="<?=$this->get_field_id( 'title' )?>" name="<?=
    $this->get_field_name( 'title' )?>"><?=wp_kses( $title,
      array( 'br' => array() ) )?></textarea>
</p>
<p>
  <label for="<?=$this->get_field_id( 'post_type' )?>">Post Type</label>
  <select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>"
    name="<?=$this->get_field_name( 'post_type' )?>">
<?php foreach( $post_types as $value => $label ): ?>
    <option value="<?=esc_attr( $value )?>"<?=( $value == $post_type )
      ? ' selected' : '' ?>><?=esc_html( $label )?></option>
<?php endforeach; ?>
  </select>
</p>
<p>
  <label for="<?=$this->get_field_id( 'classes' )?>">Additional CSS Class(es)</label>
  <input class="widefat" id="<?php echo $this->get_field_id( 'classes' ); ?>" name="<?=
    $this->get_field_name( 'classes' )?>" type="text" value="<?php
      echo esc_attr( $classes ); ?>">
</p>
<?php
  }


  // Widget Backend - Save changes
  public function update( $new_instance, $old_instance )
  {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) )
      ? wp_kses( $new_instance['title'], array( 'br' => array() ) ) : '';
    $instance['post_type'] = ( ! empty( $new_instance['post_type'] ) )
      ? strip_tags( $new_instance['post_type'] ) : '';
    $instance['classes'] = ( ! empty( $new_instance['classes'] ) )
      ? strip_tags( $new_instance['classes'] ) : '';
    return $instance;
  }

}
// end: FH_Logos_Grid