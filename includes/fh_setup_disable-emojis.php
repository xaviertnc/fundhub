<?php

/**
 *  Remove emoji library from Wordpress.
 */
function fh_disable_emojis() {
    // Misc stuff - Needs own function!
    remove_action( 'wp_head', 'dns_prefetch' );
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head');
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );
    remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );

    // Let's remove a bunch of actions & filters.
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

    // We also take care of Tiny MCE.
    add_filter( 'tiny_mce_plugins', 'fh_disable_emojis_tinymce' );
    add_filter( 'wp_resource_hints', 'fh_disable_emojis_remove_dns_prefetch', 10, 2 );
}

add_action( 'init', 'fh_disable_emojis' );


/**
* Filter funcion to remove the emoji plugin from TinyMCE.
* @param array $plugins
* @return array Difference betwen the two arrays.
*/
function fh_disable_emojis_tinymce($plugins) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else return array();
}


/**
* Removing emoji CDN hostname from DNS prefetching hints.
* @param array $urls URLs to print for resource hints.
* @param string $relation_type The relation type the URLs are printed for.
* @return array Difference betwen the two arrays.
*/
function fh_disable_emojis_remove_dns_prefetch( $urls, $relation_type )
{

    // if ( 'dns-prefetch' == $relation_type ) {
    //     /** This filter is documented in wp-includes/formatting.php */
    //     $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
    //     $urls = array_diff( $urls, array( $emoji_svg_url ) );
    // }
    // return $urls;

    if ( 'dns-prefetch' === $relation_type ) {
		$result = array();
		foreach ( $urls as $url )
		{
		    if ( strpos( 'emoji', $url ) < 0 )
		    {
		        $result[] = $url;
		    }
		}
		return $result;
    }

    return $urls;
}