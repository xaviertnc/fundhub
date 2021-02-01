<?php
/**
 * fh-widget-image.php
 * 
 * Fund Hub Image Widget
 * 
 * Theme: FUNDHUB
 * Author: Neels Moller
 * Version 1.0.0
 * 
 */
class FH_Image extends WP_Widget {

  public function __construct()
  {
    parent::__construct(
  
      // Base ID of your widget
      'fh_image', 
  
      // Widget name will appear in UI
      __('FundHub: Image', 'fundhub'), 

      array( 'description' => __( 'Simple image widget', 'fundhub' ) ) 
    );
  }

  
  // Widget Frontend - Render
  public function widget( $args, $instance )
  {
    $title = apply_filters('widget_title', $instance['title'] );
    $link_class  = isset($instance['class'])  ? $instance['class'] : '';
    $image_uri  = isset($instance['image_uri'])  ? $instance['image_uri'] : '';
    $click_link  = isset($instance['link'])  ? $instance['link'] : '';
    echo $args['before_widget'];
    if( $click_link ): ?>

    <a class="<?=$link_class?>" href="<?=esc_url($click_link)?>" target="_blank" >
      <img src="<?=esc_url($image_uri)?>" alt="<?=esc_attr($title)?>" />
    </a>
<?php else: ?>

    <div class="<?=$link_class?>">
      <img src="<?=esc_url($image_uri)?>" alt="<?=esc_attr($title)?>" />
    </div>
<?php endif;

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
    if( isset( $instance[ 'link' ] ) )
    {
      $link = $instance[ 'link' ];
    }
    else
    {
      $link = '';
    }
    if( isset( $instance[ 'class' ] ) )
    {
      $class = $instance[ 'class' ];
    }
    else
    {
      $class = '';
    }    
    if( isset( $instance[ 'image_uri' ] ) )
    {
      $image_uri = $instance[ 'image_uri' ];
    }
    else
    {
      $image_uri = '';
    }     
?>

<p>
  <label for="<?=$this->get_field_id( 'image_uri' )?>">Image</label> 
  <img class="<?=$this->id?>_img" src="<?=esc_attr( $image_uri )?>"
    style="margin:0;padding:0;max-width:100%;display:block">
  <input class="widefat <?= $this->id ?>_url" name="<?=
    $this->get_field_name( 'image_uri' )?>" type="text" value="<?=
      esc_attr( $image_uri )?>" placeholder="https://" style="margin-top:5px">
  <input type="button" id="<?=$this->id?>"
    class="button button-primary js_custom_upload_media" value="Upload Image"
      style="margin-top:5px;">
</p>
<p>
  <label for="<?=$this->get_field_id( 'title' )?>">Alt Text</label> 
  <input class="widefat" id="<?=$this->get_field_id( 'title' )?>" name="<?=
    $this->get_field_name( 'title' )?>" type="text" value="<?php
      echo esc_attr( $title ); ?>">
</p>
<p>
  <label for="<?=$this->get_field_id( 'link' )?>">Click Link</label> 
  <input class="widefat" id="<?=$this->get_field_id( 'link' )?>" name="<?=
    $this->get_field_name( 'link' )?>" type="text" value="<?php
      echo esc_attr( $link ); ?>" placeholder="https://">
</p>
<p>
  <label for="<?=$this->get_field_id( 'class' )?>">Additional CSS Class(es)</label> 
  <input class="widefat" id="<?php echo $this->get_field_id( 'class' ); ?>" name="<?=
    $this->get_field_name( 'class' )?>" type="text" value="<?php
      echo esc_attr( $class ); ?>">
</p>

<?php
  }


  // Widget Backend - Save changes
  public function update( $new_instance, $old_instance )
  {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) )
      ? strip_tags( $new_instance['title'] ) : '';
    $instance['link'] = ( ! empty( $new_instance['link'] ) )
      ? strip_tags( $new_instance['link'] ) : '';      
    $instance['class'] = ( ! empty( $new_instance['class'] ) )
      ? strip_tags( $new_instance['class'] ) : '';
    $instance['image_uri'] = ( ! empty( $new_instance['image_uri'] ) )
      ? strip_tags( $new_instance['image_uri'] ) : '';
    return $instance;
  }	
}
