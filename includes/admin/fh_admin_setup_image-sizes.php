<?php  /* FUND HUB Admin Setup Image Sizes */


// Disable scaled image sizes
// add_filter('big_image_size_threshold', '__return_false');


function fh_disable_image_sizes($sizes)
{
  //unset($sizes['thumbnail']);
  //unset($sizes['medium']);
  //unset($sizes['large']);
  //unset($sizes['medium_large']);
  //unset($sizes['1536x1536']);    // disable 2x medium-large size
  //unset($sizes['2048x2048']);    // disable 2x large size
  //return $sizes;
  return array();
}

add_action('intermediate_image_sizes_advanced', 'fh_disable_image_sizes');