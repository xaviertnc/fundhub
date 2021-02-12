<?php // FUND HUB :: Asset Manager Custom Post :: Publish ASM Profiles Class

class FH_Publish_Profiles {

public $user_id;
public $site_id;
public $upload_info;


public function arrayGet( $arr = null, $key = null, $default = null ) {
  if ( ! $arr ) { return; }
  if ( ! $key ) { return $arr; }
  return isset( $arr[$key] ) ? $arr[$key] : $default;
}


public function getInput( $key, $default = null ) {
  return isset( $_REQUEST[$key] ) ? $_REQUEST[$key] : $default;
}


public function urlGetContents ( $url ) {
  if ( function_exists( 'curl_exec' ) ) {
    $conn = curl_init( $url );
    curl_setopt( $conn, CURLOPT_SSL_VERIFYPEER, true );
    curl_setopt( $conn, CURLOPT_FRESH_CONNECT,  true );
    curl_setopt( $conn, CURLOPT_RETURNTRANSFER, 1    );
    $url_get_contents_data = ( curl_exec( $conn ) );
    curl_close( $conn );
  } elseif ( function_exists( 'file_get_contents' ) ) {
    $url_get_contents_data = file_get_contents( $url );
  } elseif ( function_exists( 'fopen' ) && function_exists( 'stream_get_contents' ) ) {
    $handle = fopen( $url, 'r' );
    $url_get_contents_data = stream_get_contents( $handle );
  } else {
    $url_get_contents_data = false;
  }
  return $url_get_contents_data;
}


public function verifyNonce( $action ) {
  $nonce = $this->getInput( 'fh_asm_nonce' );
  if ( wp_verify_nonce( $nonce , $action ) ) { return $action; }
  wp_die('invalid nonce');
}


public function isAction( $action ) {
  return $this->getInput( 'do' ) == $action ? $this->verifyNonce( $action ) : false;
}


public function getSites( array $filter = [] ) {
  $args = array( 'public' => 1, 'deteted' => 0 );
  if ( isset( $filter['only'] ) ) { $args['site__in'] = $filter['only']; }
  if ( isset( $filter['excl'] ) ) { $args['site__not_in'] = $filter['excl']; }
  return get_sites( $args );
}


public function getAttachments( array $filter = [] ) {
  $args = array(
    'order' => 'ASC',
    'post_type' => 'attachment',
    'post_mime_type' => 'image'
  );
  if ( isset( $filter['parent_id'] ) )
  {
    $args['post_parent'] = $filter['parent_id'];
  }
  if ( isset( $filter['only'] ) )
  {
    $args['post__in'] = $filter['only'];
  }
  //echo '<pre>', print_r($args, true), '</pre>';
  return get_posts($args);
}


public function getManagers( array $filter = [] ) {
  $args = array(
    'post_type'      => 'portfolio',
    'post_status'    => 'publish',
    'posts_per_page' => -1
  );
  if ( isset( $filter['only'] ) )
  {
    $args['post__in'] = $filter['only'];
  }
  if ( isset( $filter['user_id'] ) )
  {
    $args['author'] = $filter['user_id'];
  }
  $query_result = new WP_Query($args);
  $managers = array();
  if ( isset( $filter['tag'] ) )
  {
    $tag_name = $filter['tag'];
    foreach ( $query_result->posts as $manager )
    {
	  $tags = get_the_terms( $manager->ID, 'portfolio_tag' );
      //echo '<pre>', print_r($tags, true), '</pre>';
      if ( ! empty( $tags ) and ! is_wp_error( $tags ) ) {
        foreach ( $tags as $post_tag )
        {
          if ( $post_tag->name == $tag_name )
          {
            $managers[] = $manager;
            break;
          }
        }
      }
    }
  }
  else {
    $managers = $query_result->posts;
  }
  return $managers;
}


public function getManagerMetas( $manager_id, array $filter = [] ) {
  $metas = get_post_meta( $manager_id );
  $results = array();
  foreach ( $metas as $metaKey => $arrVal )
  {
    $metaVal = $arrVal[0];
    $keepFilter = isset( $filter['keep'] ) ? $filter['keep'] : [];
    foreach ( $keepFilter as $opt => $optVal )
    {
      if ( $opt == 'startsWith' )
      {
        foreach ( ( is_array( $optVal ) ? $optVal : array( $optVal ) ) as $startsWith )
        {
          if ( strpos( $metaKey, $startsWith ) === 0 )
          {
            $results[$metaKey] = $metaVal;
            continue 3;
          }
        }
      }
    }
    $ignoreFilter = isset( $filter['ignore'] ) ? $filter['ignore'] : [];
    foreach ( $ignoreFilter as $opt => $optVal )
    {
      if ( $opt == 'novalue' and !$metaVal and $metaVal !== 0 )
      {
        continue 2;
      }
      elseif ( $opt == 'startsWith' )
      {
        foreach ( ( is_array( $optVal ) ? $optVal : array( $optVal ) ) as $startsWith )
        {
          if ( strpos( $metaKey, $startsWith ) === 0 ) { continue 3; }
        }
      }
    }
    $results[$metaKey] = $metaVal;
  }
  return $results;
}


public function getManagerImages( $manager_id, $thumb_id, $top_img_id ) {
  //$filter = array ( 'parent_id' => $manager_id, 'only' => array( $thumb_id, $top_img_id ) );
  $filter = array ( 'only' => array( $thumb_id, $top_img_id ) );
  return $this->getAttachments( $filter );
}


public function deleteImage( $post_id, $skip_trash = false ) {
  return wp_delete_attachment( $post_id, $skip_trash );
}


public function dropKeys( array $arr, array $drop_keys = [] ) {
  return array_filter( $arr, function( $meta_key ) use ( $drop_keys ) {
    return ! in_array( $meta_key, $drop_keys );
  }, ARRAY_FILTER_USE_KEY );
}


public function updatePostMetas( $post_id, array $metas = [] ) {
  $unhappy = [];
  foreach ( $metas as $meta_key => $meta_value ) {
    if ( ! update_metadata( 'post', $post_id, $meta_key, $meta_value ) ) {
      $unhappy[$meta_key] = $meta_value;
    }
  }
  return $unhappy;
}


public function copyAndAttachImageTo( $target_post_id, $image_data, $image_meta ) {

  $target_upload_info = wp_upload_dir();

  echo '<hr><b>Copy Source Image And Attach To Target Post</b><br>';

  $src_path = $this->arrayGet( $this->upload_info , 'basedir' );
  $tgt_path = $this->arrayGet( $target_upload_info, 'path' );
  $src_file = $this->arrayGet( $image_meta, 'file' );

  $width = $this->arrayGet( $image_meta, 'width' );
  $height = $this->arrayGet( $image_meta, 'height' );

  $src_filename = basename( $src_file );
  $unique_tgt_filename = wp_unique_filename( $tgt_path, $src_filename );

  $src_file = "$src_path/$src_file";
  $tgt_file = "$tgt_path/$unique_tgt_filename";

  echo 'Width  = ', $width , '<br>';
  echo 'Height = ', $height, '<br>';
  //echo 'Source Path = ', $src_path, '<br>';
  //echo 'Target Path = ', $tgt_path, '<br>';
  echo 'Src File    = ', $src_file, '<br>';
  echo 'Tgt File    = ', $tgt_file, '<br>';
  echo 'Src File Exists  = ', ( file_exists( $src_file ) ? 'YES' : 'NO' ), '<br>';

  if ( @copy( $src_file, $tgt_file ) ) {

    $tgt_image_data = $this->dropKeys( (array) $image_data, array( 'ID' ) );

    $tgt_image_data['post_author'] = $this->user_id;
    $tgt_image_data['post_parent'] = $target_post_id;

    $tgt_image_id = wp_insert_attachment( $tgt_image_data, $tgt_file, $target_post_id );
    echo 'New Image ID: ', $tgt_image_id, '<br>';

    if ( ! empty( $tgt_image_id ) and ! is_wp_error( $tgt_image_id ) ) {

      $tgt_image_meta = $this->dropKeys( $image_meta, array( 'sizes', 'file' ) );

      $tgt_image_meta['sizes'] = array();
      $tgt_image_meta['file'] = ltrim( $this->arrayGet( $target_upload_info, 'subdir' ), '/' ) . '/' . $unique_tgt_filename;

      if ( wp_update_attachment_metadata( $tgt_image_id,  $tgt_image_meta ) ) {

        //echo 'Image Data: <pre>', print_r( $tgt_image_data, true), '</pre>';
        //echo 'Image Meta: <pre>', print_r( $tgt_image_meta, true), '</pre>';
        echo 'New Image Metas - Added<br>';

      } else {
        echo '<b style="color:red">ERROR. Could not update attachment meta data: <pre>',
          print_r( $tgt_image_meta, true), '</pre></b>';
      }

    } else {
      echo '<b style="color:red">ERROR. Could not insert new image attachment: <pre>',
        print_r( $tgt_image_data, true), '</pre></b>';
    }

    return $tgt_image_id;

  } else {
    echo '<b style="color:red">ERROR. Could not copy src file to: ', $tgt_file, '</b><br>';
  }

}


public function insertPost( $src_post, $src_post_metas, $src_post_images ) {

  //echo '<hr><b>Insert New Post and Images</b><br>';

  echo '<b>CREATE POST USING SOURCE DATA</b><br>';
  $target_post_data = $this->dropKeys( (array) $src_post, array( 'ID' ) );
  $target_post_data['post_author'] = $this->user_id;

  $terms = get_terms( array(
    'taxonomy' => 'portfolio_category',
    'hide_empty' => false,
    'name' => 'Investors'
  ) );

  $term_ids = $terms ? array( (int) $terms[0]->term_id ) : array();
  $target_post_data['tax_input'] = array( 'portfolio_category' => $term_ids );
  echo 'New Post Category Term IDs: ', implode( ', ', $term_ids), '<br>';

  //echo 'New Post Data: <pre>', print_r( $target_post_data, true), '</pre>';
  $target_post_id = wp_insert_post( $target_post_data, true );
  echo 'New Asset Manager ID: ', $target_post_id, '<br>';

  if ( empty( $target_post_id ) or is_wp_error( $target_post_id ) ) {
    echo '<b style="color:red">ERROR. Could not insert new Asset Manager post: <pre>',
      print_r( $target_post_id, true), '</pre></b>';
  }
  else
  {
    $thumb_id = null;
    $top_img_id = null;

    $target_post_metas = $this->dropKeys( $src_post_metas, array( '_thumbnail_id', 'top_image' ) );

    // Add Post Thumbnail
    if ( $src_post_images['thumb'] )
    {
      $thumb_id = $this->copyAndAttachImageTo( $target_post_id,
        $src_post_images['thumb'], $src_post_images['thumb_meta'] );
      $target_post_metas['_thumbnail_id'] = (int) $thumb_id;
    }

    // Add Post Top Image
    if ( $src_post_images['top'] )
    {
      $top_img_id = $this->copyAndAttachImageTo( $target_post_id,
        $src_post_images['top'], $src_post_images['top_meta'] );
      $target_post_metas['top_image'] = (int) $top_img_id;
    }

    // Add Post Metas
    //echo 'New Post Metas: <pre>', print_r( $target_post_metas, true), '</pre>';
    echo '<hr><b>ADD POST METAS SOURCE METAS</b><hr>';
    $this->updatePostMetas( $target_post_id, $target_post_metas );
  }

  return $target_post_id;
}


public function main() {

  $this->user_id = get_current_user_id();
  $this->site_id = get_current_blog_id();

  $this->upload_info = wp_upload_dir();


  //// POST CONTROLLER ////
  if ( $action = $this->isAction( 'publish' ) )
  {
    echo '<h1>PUBLISH ASSET MANAGER POSTS - REPORT</h1>';

    $selected_site_ids = $this->getInput( 'selected_sites', [] );
    // $selected_sites = $this->getSites( array( 'only' => $selected_site_ids ) );

    $selected_manager_ids = $this->getInput( 'selected_managers', [] );
    $selected_managers = $this->getManagers( array( 'only' => $selected_manager_ids ) );

    // echo 'REQUEST:<pre>', print_r( $_REQUEST, true ), '</pre>';
    echo '<hr><h3>SELECTED ASSET MANAGERS</h3>';
    echo '<b>POST IDS:</b> ', implode( ', ', $selected_manager_ids ), '<hr>';

    $selected_manager_metas = [];
    $selected_manager_img_ids = [];
    $selected_manager_images = [];

    $keep = array( 'startsWith' => array ( '_thumb', '_top_image' ) );
    $ignore = array ( 'startsWith' => array( '_', 'wpex', 'ma_', 'um_' ) );
    $metasFilter = array( 'keep' => $keep, 'ignore' => $ignore );


    /// LOOP THROUGH AND GET SELECTED POSTS META DATA + IMAGES ////
    foreach ( $selected_managers as $index => $manager)
    {
      echo '<b>', ($index + 1), '. SELECTED ASSET MANAGER - POST ID: ', $manager->ID, '</b><br>',
        'NAME: ', $manager->post_name, '<br>',
        'GUID: ', $manager->guid, '<br>';

      $manager_metas = $this->getManagerMetas( $manager->ID, $metasFilter );

      $top_img_id = $this->arrayGet( $manager_metas, 'top_image' );
      $thumb_id = $this->arrayGet( $manager_metas, '_thumbnail_id' );

      $tmp_images = $this->getManagerImages($manager->ID, $thumb_id, $top_img_id);

      $manager_image_ids = array ( 'thumb_id' => null,  'top_img_id' => null );
      $manager_images = array ( 'thumb' => null, 'top' => null );
      foreach ( $tmp_images as $image ) {
        if ( $image->ID == $thumb_id ) {
          $manager_image_ids['thumb_id'] = $thumb_id;
          $manager_images['thumb'] = $image;
          $manager_images['thumb_meta'] = wp_get_attachment_metadata( $image->ID, 'unfiltered' );
        }
        if ( $image->ID == $top_img_id ) {
          $manager_image_ids['top_img_id'] = $top_img_id;
          $manager_images['top'] = $image;
          $manager_images['top_meta'] = wp_get_attachment_metadata( $image->ID, 'unfiltered' );
        }
      }

      $selected_manager_metas[$manager->ID] = $manager_metas;
      $selected_manager_img_ids[$manager->ID] = $manager_image_ids;
      $selected_manager_images[$manager->ID] = $manager_images;

      echo 'IMAGES: ', print_r( $manager_image_ids, true), '<br><hr>';
      // echo 'SELECTED MANAGER IMAGE POSTS = <pre>', print_r( $manager_images, true), '</pre>';

      $tmp_images = [];
    }

    //echo 'SELECTED MANAGER IMAGE IDS = <pre>', print_r( $selected_manager_img_ids, true), '</pre>';

    echo '<h3>SELECTED SITES</h3>';
    echo '<b>SITE IDS:</b> ', implode( ', ', $selected_site_ids ), '<hr>';

    $site_split_lists = array();


    /// LOOP THROUGH SELECTED SITES AND UPDATE OR INSERT POSTS + IMAGES ////
    foreach ( $selected_site_ids as $target_site_id )
    {
      $new     = array();
      $updates = array();

      echo '<h3>TARGET SITE ', $target_site_id, '</h3>';

      switch_to_blog( $target_site_id ); // NB!

      $target_site_managers = $this->getManagers();

      //// SPLIT POSTS INTO UPDATES AND INSERTS! ////
      foreach ( $selected_managers as $index => $source_manager)
      {
        echo ($index + 1), '. CHECK SELECTED MANAGER - POST ID: ',
          $source_manager->ID, ', NAME: ', $source_manager->post_name, '<br>';

        foreach ( $target_site_managers as $target_manager )
        {
          if ( $target_manager->guid == $source_manager->guid )
          {
            echo '<b style="color:navy"> -> FOUND MATCHING TARGET POST ID: ',
              $target_manager->ID, ', NAME: ',  $target_manager->post_name,
              ' - UPDATE</b><hr>';

            $updates[] = array ( 'src' => $source_manager, 'target' => $target_manager );
            continue 2;
          }
        }

        $new[] = $source_manager;

        echo '<b style="color:darkgreen"> -> NO MATCHING TARGET POST - INSERT</b><hr>';
      }

      //// INSERT POSTS ////
      echo '<h3>Insert New Posts ( Site ', $target_site_id, ' ):</h3>';
      // Cycle through asset managers to update on the current target site
      foreach ( $new as $index => $src_manager )
      {
        $src_metas = $selected_manager_metas[$src_manager->ID];
        $src_images = $selected_manager_images[$src_manager->ID];
        $src_img_ids = $selected_manager_img_ids[$src_manager->ID];

        echo '<b style="color:darkgreen">', ( $index + 1 ), '. INSERT ASSET MANAGER, NAME: ',
          $src_manager->post_name, '</b><br>';
        echo 'Images: ', implode( ', ', $src_img_ids ), '<hr>';

        /// Insert New Asset Manager
        $target_manager_id = $this->insertPost( $src_manager, $src_metas, $src_images );
        echo 'Newly added post id = ', print_r( $target_manager_id, true ), '<hr>';

      }
      if ( count( $new ) == 0 ) {
        echo '<span style="color:darkgreen">** Nothing new. **</span><hr>';
      }

      //// UPDATE POSTS ////
      echo '<h3>Update Existing Posts ( Site ', $target_site_id, ' ):</h3>';
      // Cycle through asset managers to update on the current target site
      foreach ( $updates as $index => $update )
      {
        $src_manager = $update['src'];
        $src_metas = $selected_manager_metas[$src_manager->ID];
        $src_images = $selected_manager_images[$src_manager->ID];
        $src_img_ids = $selected_manager_img_ids[$src_manager->ID];

        $target_manager = $update['target'];
        $target_metas = $this->getManagerMetas( $target_manager->ID, $metasFilter );

        $target_top_img_id = $this->arrayGet( $target_metas, 'top_image' );
        $target_thumb_id = $this->arrayGet( $target_metas, '_thumbnail_id' );

        $tmp_images = $this->getManagerImages($target_manager->ID,
          $target_thumb_id, $target_top_img_id);

        $target_image_ids = array ( 'thumb_id' => null,  'top_img_id' => null );
        $target_images = array ( 'thumb' => null, 'top' => null );
        foreach ( $tmp_images as $image ) {
          if ( $image->ID == $target_thumb_id ) {
            $target_image_ids['thumb_id'] = $target_thumb_id;
            $target_images['thumb'] = $image;
          }
          elseif ( $image->ID == $target_top_img_id ) {
            $target_image_ids['top_img_id'] = $target_top_img_id;
            $target_images['top'] = $image;
          }
        }

        echo '<b style="color:navy">', ( $index + 1 ), '. UPDATE TARGET POST ID: ',
          $target_manager->ID, ', NAME: ', $target_manager->post_name, '</b><br>';


        echo 'TARGET POST IMAGES: ', implode( ', ', $target_image_ids ), '<br>';
        // echo 'TARGET MANAGER IMAGES = <pre>', print_r( $target_images, true), '</pre>';

        // UPDATE OR REMOVE THUMB
        if ( $target_images['thumb'] )
        {
          echo '<hr>TARGET POST HAS THUMBNAIL<br>';
          if ( empty( $src_images['thumb'] ) )
          {
            echo 'SRC POST DON\'T HAVE A THUMBNAIL!  DELETE TRGT THUMB...<br>';
            $this->deleteImage( $target_images['thumb']->ID );
            delete_post_thumbnail( $target_manager->ID );
          }
          elseif ( $target_images['thumb']->guid != $src_images['thumb']->guid )
          {
            echo '<hr>TRGT THUMB <> SRC THUMB. REPLACE TRGT THUMB...<br>';
            echo 'SRC THUMB: <pre>', print_r( $src_images['thumb'], true), '</pre>';
            echo 'TARGET THUMB: <pre>', print_r( $target_images['thumb'], true), '</pre>';
            $this->deleteImage( $target_images['thumb']->ID );
            $tgt_image_id = $this->copyAndAttachImageTo( $target_manager->ID,
              $src_images['thumb'], $src_images['thumb_meta'] );
            set_post_thumbnail( $target_manager->ID, (int) $tgt_image_id );
          }
        }
        elseif ( isset( $src_images['thumb'] ) )
        {
          echo '<hr>TARGET POST HAS NO THUMBNAIL! ADD ONE...<br>';
          $tgt_image_id = $this->copyAndAttachImageTo( $target_manager->ID,
            $src_images['thumb'],  $src_images['thumb_meta'] );
          set_post_thumbnail( $target_manager->ID, (int) $tgt_image_id );
        }

        // UPDATE OR REMOVE TOP IMAGE
        if ( $target_images['top'] )
        {
          echo '<hr>TARGET POST HAS TOP IMG<br>';
          if ( empty( $src_images['top'] ) )
          {
            echo 'SRC POST DON\'T HAVE A TOP IMG!  DELETE TRGT TOP IMG...<br>';
            $this->deleteImage( $target_images['top']->ID );
            update_post_meta( $target_manager->ID, 'top_image', null );
          }
          elseif ( $target_images['top']->guid != $src_images['top']->guid )
          {
            echo 'TRGT TOP <> SRC TOP. REPLACE TRGT TOP IMG...<br>';
            $this->deleteImage( $target_images['top']->ID );
            $tgt_image_id = $this->copyAndAttachImageTo( $target_manager->ID,
              $src_images['top'], $src_images['top_meta'] );
            update_post_meta( $target_manager->ID, 'top_image', (int) $tgt_image_id );
          }
        }
        elseif ( isset( $src_images['top'] ) )
        {
          echo '<hr>TARGET POST HAS NO TOP IMG! ADD ONE...<br>';
          $tgt_image_id = $this->copyAndAttachImageTo( $target_manager->ID,
            $src_images['top'], $src_images['top_meta'] );
          update_post_meta( $target_manager->ID, 'top_image', (int) $tgt_image_id );
        }

        // UPDATE TARGET SITE MANAGER POST
        echo '<hr>UPDATE TARGET SITE MANAGER POST<br>';
        $tgt_data_to_update = $this->dropKeys( (array) $src_manager, array( 'ID', 'post_author' ) );
        $tgt_data_to_update['ID'] = $target_manager->ID;
        $tgt_data_to_update['post_author'] = $this->user_id;
        // echo 'NEW DATA: <pre>', print_r( $tgt_data_to_update, true), '</pre>';
        if ( ! wp_update_post( $tgt_data_to_update ) )
        {
          echo '<b style="color:red">ERROR updating new post with: <pre>',
            print_r( $tgt_data_to_update, true), '</pre></b>';
        }

        // UPDATE TARGET SITE MANAGER POST METAS
        echo '<hr>UPDATE TARGET SITE MANAGER POST METAS<br>';
        $tgt_metas_to_update = $this->dropKeys( $src_metas, array( '_thumbnail_id', 'top_image' ) );
        $this->updatePostMetas( $target_manager->ID, $tgt_metas_to_update );

        echo '<hr>';

      }
      if ( count( $updates ) == 0 ) {
        echo '<span style="color:navy">** Nothing to update. **</span><hr>';
      }

      // Not sure if this allocation will be needed.  Only to display a
      // summary out-side the main loop for now.
      $split_list = array( 'updates' => $updates, 'new' => $new );
      $site_split_lists["site_$target_site_id"] = $split_list;

      restore_current_blog();
    }

    // echo 'SPLIT LISTS:<pre>', print_r( $site_split_lists, true ), '</pre>';

    exit;
  }



  //// GET CONTROLLER ////
  $sites = $this->getSites( array( 'excl' => array( $this->site_id ) ) );
  //$managers = $this->getManagers( array( 'tag' => 'Publish' ) );
  $managers = $this->getManagers();

?>

<style>
  table {
    background-color: white;
    border-collapse: collapse;
    border: 1px solid gainsboro;
    margin: 1em 0;
    width: 98%;
  }
  td { padding: 7px; }
  #fh-form label {
    display: block;
    padding: 0 0.5em;
    margin: 0.5em 0;
  }
  .row {
    display: flex;
    flex-wrap: wrap;
  }
  .col {
    flex: 1;
    min-width: 380px;
  }
  .btn { padding: 7px; }
</style>

<form id="fh-form" method="post">
  <div class="row">
  <div class="col">
  <h1>Select ASM Profiles to Publish</h1>
  <label style="background-color:white;padding:0.5em">
    <input type="checkbox" onclick="var self=this;document.getElementById('asm-list').querySelectorAll('input').forEach(cb => cb.checked = self.checked);">
    <b>All</b>
  </label>
  <div id="asm-list">
<?php
  foreach ( $managers ?: array() as $manager ):
    $keep = array( 'startsWith' => array ( '_thumb' ) );
    $ignore = array ( 'startsWith' => array( '_', 'wpex', 'ma_', 'um_' ), 'novalue' );
    $metasFilter = array( 'keep' => $keep, 'ignore' => $ignore );
    $metas = $this->getManagerMetas( $manager->ID, $metasFilter );

    $top_img_id = $this->arrayGet( $metas, 'top_image' );
    $thumb_id = $this->arrayGet( $metas, '_thumbnail_id' );
    $images = $this->getManagerImages($manager->ID, $thumb_id, $top_img_id);
?>
  <label onclick="document.getElementById('meta_<?=$manager->ID?>').classList.toggle('hidden');">
    <input type="checkbox" name="selected_managers[]" value="<?=$manager->ID?>">
      <?=$manager->ID?> - <?=$manager->post_title?>
  </label>
  <div id="meta_<?=$manager->ID?>" class="hidden">
    <table>
      <tbody>
<?php foreach ( $metas ?: array() as $key => $val ): ?>
        <tr>
          <td style="color:blue;min-width:11em"><b><?=$key?></b></td>
          <td style="width:100%"><?=htmlspecialchars($val)?></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
    <table>
      <tbody>
<?php foreach ( $images ?: array() as $image ): ?>
<?php $img_url = wp_get_attachment_url($image->ID); ?>
        <tr>
          <td style="color:blue;min-width:11em">
            <?=$image->ID?><br>
            <?=$image->ID == $thumb_id ? 'Thumbnail' : 'Top Image'?>
          </td>
          <td style="width:100%">
            <img src="<?=$img_url?>"><br>
            <small><a href="<?=$img_url?>" target="_blank"><?=$img_url?></a></small>
          </td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
  </div><!-- end: profiles-list -->
  <br>
  </div><!-- end:col -->
  <div class="col">
  <h1>Partner Sites</h1>
  <label style="background-color:white;padding:0.5em">
    <input type="checkbox" onclick="var self=this;document.getElementById('sites-list').querySelectorAll('input').forEach(cb => cb.checked = self.checked);">
    <b>All</b>
  </label>
  <div id="sites-list">
<?php foreach ( $sites ?: array() as $site ): ?>
  <label><input type="checkbox" name="selected_sites[]" value="<?= $site->blog_id?>">
    <?= $site->blog_id?> - <?=$site->domain?><?=$site->path?></label>
<?php endforeach; ?>
  </div><!-- end: sites-list -->
  <br>
  <?php wp_nonce_field( 'publish', 'fh_asm_nonce' ); ?>

  <button class="btn btn-primary" name="do" value="publish">Publish to Selected Partner Sites</button>
  </div><!-- end:col -->
  </div><!-- end: row -->
</form>

<?php
}
// end:main()

}
// end:FH_Publish_Profiles Class
