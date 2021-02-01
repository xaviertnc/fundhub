<?php
/**
 * fh-widget-callout.php
 * 
 * Fund Hub Callout (Call to Action) Widget
 * 
 * Theme: FUNDHUB
 * Author: Neels Moller
 * Version 2.0.0
 * 
 */
class FH_Callout extends WP_Widget {
  
  public function __construct()
  {
    parent::__construct(
  
      // Base ID of your widget
      'fh_callout', 
  
      // Widget name will appear in UI
      __('FundHub: Call To Action', 'fundhub'), 

      array( 'description' => __( 'Call to action widget', 'fundhub' ) ) 
    );
  }

  
  // Widget Frontend - Render
  public function widget( $args, $instance )
  {
    $title = apply_filters('widget_title', $instance['title'] );
    $image_alt = isset( $instance[ 'image_alt' ] ) ? $instance[ 'image_alt' ] : '';
    $image_uri = isset( $instance[ 'image_uri' ] ) ? $instance[ 'image_uri' ] : '';
    $link  = isset( $instance[ 'link' ] )  ? $instance[ 'link' ] : '';
    $class = isset( $instance[ 'class' ] ) ? ' ' . $instance[ 'class' ] : '';
    echo $args['before_widget'];
    // echo '<pre>', print_r($instance, true), '</pre>';
?>

    <a class="cta<?=$class?>" href="<?=$link?>" target="_blank">
      <span><?=$title?> <i class="arrow-right"></i></span>
      <img src="<?=esc_url($image_uri)?>" alt="<?=esc_attr($image_alt)?>" height="70">
    </a>
<?php

    echo $args['after_widget'];
  }


  // Widget Backend - Render settings form
  public function form( $instance )
  {
    if( isset( $instance[ 'image_alt' ] ) )
    {
      $image_alt = $instance[ 'image_alt' ];
    }
    else
    {
      $image_alt = __( 'Click here to manage your CPD points', 'fundhub' );
    }    
    if( isset( $instance[ 'image_uri' ] ) )
    {
      $image_uri = $instance[ 'image_uri' ];
    }
    else
    {
      $image_uri = '';
    }    
    if( isset( $instance[ 'title' ] ) )
    {
      $title = $instance[ 'title' ];
    }
    else
    {
      $title = __( 'Earn CPD Points', 'fundhub' );
    }
    if( isset( $instance[ 'link' ] ) )
    {
      $link = $instance[ 'link' ];
    }
    else
    {
      $link = 'https://thecpdhub.co.za';
    }    
    if( isset( $instance[ 'class' ] ) )
    {
      $class = $instance[ 'class' ];
    }
    else
    {
      $class = 'cta-the-cpd-hub';
    }    
?>

<p>
  <label for="<?=$this->get_field_id( 'image_uri' )?>">CTA Image</label> 
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
  <label for="<?=$this->get_field_id( 'image_alt' )?>">CTA Image Alt:</label> 
  <input class="widefat" id="<?=$this->get_field_id( 'image_alt' )?>" name="<?=
    $this->get_field_name( 'image_alt' )?>" type="text" value="<?php
      echo esc_attr( $image_alt ); ?>">
</p>
<p>
  <label for="<?=$this->get_field_id( 'title' )?>">CTA Action Text:</label> 
  <input class="widefat" id="<?=$this->get_field_id( 'title' )?>" name="<?=
    $this->get_field_name( 'title' )?>" type="text" value="<?php
      echo esc_attr( $title ); ?>">
</p>
<p>
  <label for="<?=$this->get_field_id( 'link' )?>">CTA Action Link:</label> 
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
    $instance['image_uri'] = ( ! empty( $new_instance['image_uri'] ) )
      ? strip_tags( $new_instance['image_uri'] ) : '';    
    $instance['image_alt'] = ( ! empty( $new_instance['image_alt'] ) )
      ? strip_tags( $new_instance['image_alt'] ) : '';    
    $instance['title'] = ( ! empty( $new_instance['title'] ) )
      ? strip_tags( $new_instance['title'] ) : '';
    $instance['link'] = ( ! empty( $new_instance['link'] ) )
      ? strip_tags( $new_instance['link'] ) : '';
    $instance['class'] = ( ! empty( $new_instance['class'] ) )
      ? strip_tags( $new_instance['class'] ) : '';
    return $instance;
  }
 

}
// end: FH_Callout