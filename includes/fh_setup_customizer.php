<?php  /* FUND HUB Theme Customizer Setup */


function fh_customize_register( $wp_customize )
{
  $wp_customize->add_section( 'fundhub_scripts', array(
    // 'panel' => 'fundhub_settings',
    'title' => 'Additional Scripts',
    'priority' => 500
  ) );

  $wp_customize->add_setting( 'fh_header_scripts', array(
      'capability' => 'edit_theme_options',
      'type' => 'option'
  ) );

  $wp_customize->add_setting( 'fh_noscripts', array(
      'capability' => 'edit_theme_options',
      'type' => 'option'
  ) );

  $wp_customize->add_setting( 'fh_footer_scripts', array(
      'capability' => 'edit_theme_options',
      'type' => 'option'
  ) );

  $wp_customize->add_control( new WP_Customize_Code_Editor_Control(
    $wp_customize, 'fh_header_scripts', array(
      'code_type' => 'htmlmixed',
      'section' => 'fundhub_scripts',
      'label' => __( 'Head Script Tags' ),
      'description' => __( 'Paste tracking and other head scripts here. e.g. &lt;script&gt;ga(...&lt;/script&gt;', 'fundhub' ),
  ) ) );

  $wp_customize->add_control( new WP_Customize_Code_Editor_Control(
    $wp_customize, 'fh_noscripts', array(
     'code_type' => 'htmlmixed',
     'section' => 'fundhub_scripts',
     'label' => __( 'No Script Tags' ),
     'description' => __( 'Add tracking and other no-script tags here. e.g. &lt;noscript&gt;...&lt;/noscript&gt;', 'fundhub' ),
  ) ) );

  $wp_customize->add_control( new WP_Customize_Code_Editor_Control(
    $wp_customize, 'fh_footer_scripts', array(
      'code_type' => 'htmlmixed',
      'section' => 'fundhub_scripts',
      'label' => __( 'Footer Script Tags' ),
      'description' => __( 'Paste footer scripts here.', 'fundhub' ),
  ) ) );
}

add_action( 'customize_register', 'fh_customize_register' );
