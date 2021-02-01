<?php /* FUND HUB Setup Widgets and Widget Areas */


function fh_widgets_init()
{
  register_sidebar(
	array(
      'name'           => __( 'Site Header', 'fundhub' ),
	  'id'             => 'sidebar-header',
	  'description'    => __( 'Add header widgets here.', 'fundhub' ),
	  'before_sidebar' => '<section class="cta-bar widget-area clr">' . PHP_EOL,
	  'after_sidebar'  => PHP_EOL . '</section>' . PHP_EOL,
	  'before_widget'  => '  <div id="%1$s" class="widget %2$s">',
	  'after_widget'   => '  </div>'
	)
  );

  register_sidebar(
	array(
      'name'           => __( 'Sidebar', 'fundhub' ),
	  'id'             => 'sidebar-main',
	  'description'    => __( 'Add sidebar widgets here.', 'fundhub' ),
	  'before_sidebar' => '<aside class="content-right widget-area" role="complementary" aria-label="Sidebar">' . PHP_EOL,
	  'after_sidebar'  => PHP_EOL . '</aside>',
	  'before_widget'  => '  <section id="%1$s" class="widget %2$s">',
	  'after_widget'   => '  </section>',
	  'before_title'   => '<span class="widget-title">',
	  'after_title'    => '</span>'
    )
  );

  register_sidebar(
	array(
      'name'          => __( 'Site Footer', 'fundhub' ),
	  'id'            => 'sidebar-site-footer',
	  'description'   => __( 'Add site footer widget(s) here.', 'fundhub' ),
	  'before_sidebar' => '<div class="widget-area" role="complementary" aria-label="Site Footer Bar">' . PHP_EOL,
	  'after_sidebar'  => PHP_EOL . '</div>',
	  'before_widget' => '<section id="%1$s" class="widget %2$s">' . PHP_EOL . PHP_EOL,
	  'after_widget'  => PHP_EOL . PHP_EOL . '</section>'
	)
  );

  register_widget( 'fh_image' );
  register_widget( 'fh_callout' );
  register_widget( 'fh_logos_grid' );
}

add_action( 'widgets_init', 'fh_widgets_init' );
