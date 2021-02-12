===============================================================================
                      FUND HUB CUSTOM WORDPRESS THEME
===============================================================================

SYSTEM

  config.php
  ----------

  define( 'WP_DEBUG_LOG', '../../tmp/wp-debug_'.date('Y-m-d').'.log' );
  define('WP_POST_REVISIONS', false );
  define('AUTOSAVE_INTERVAL', 300 ); // seconds
  define('UPLOADS', 'media');


-------------------------------------------------------------------------------


FRONT-END

  - 100% Responsive styling (1200px, 1023px, 922px, 767px)
  - 100% CSS driven menus
  - Stylish mobile menu
  - Sticky site header and navigation
  - Auto sizing (scroll sensitive) site header
  - Smooth transitions & animations


  Templates
  ---------

    1. 404 Page
    2. Default Post
    3. Default Page
    4. Taxonomy Page
    5. Full Width Page


  Accessability
  -------------

    1. Skip to content link
    2. Back to top link
    3. Clear focus rect
    4. ARIA annotations


  SEO
  ---

    1. Super clean and lean HTML
       - All WP non essential includes removed
          - DNS Prefetch
          - Emoji JS & CSS
          - Links to wordpress.com
          - Block Styles

    2. Semantic HTML layout
       - header, nav, main, section, article, footer
       - h1, h2, h3, h4 placement


-------------------------------------------------------------------------------


BACKEND

  - No deafult "Wordpress.com Events" (ET call home...) section on dashboard.
  - Extra "Featured Image", "Post ID" and "Menu Order (Ord)" fields on posts list page
  - Posts lists page default sort by: "Menu Order ASC"
  - Customizer logo image support


  Widget areas (sidebars)
  -----------------------

    1. Header Bar
    2. Main Sidebar
    3. CPD Page Sidebar
    4. Footer Bar


  Widgets
  -------

    1. Header CTA widget
    2. Asset Managers Nav widget
    3. Custom image widgdet


  Post / Page Editor
  ------------------

    1. Fund Hub specific colors
    2. Wider editor container


  Sample Content
  --------------


  Plugins
  -------

    Post Duplictor


  Tools
  -----

    Export Site
    Export Posts
    Export Pages
    Export Menus
    Export Widgets
    Export Asset Managers

    Import Site
    Import Posts
    Import Pages
    Import Menus
    Import Widgets
    Import Asset Managers

    Export Database
    Import Database

    Convert to Multi-site
    Convert to Single-site
