<?php

/**
 * 
 * Contact Form 7 - Klaviyo Integration Add-on Plugin
 * 
 * Ver 1.0
 * 
 * All rights reserved.  2021
 * 
 * MaxROI - https://maxroi.co.za
 * 
 * info@maxroi.co.za
 * 
 */

class Klaviyo_CF7_Integration {


  private static $_instance = null;


  public static function get_instance()
  {
    return self::$_instance ?: new Klaviyo_CF7_Integration();
  }


  public function init()
  {
    //add_action( 'admin_menu', array( $this, 'add_menuitem' ) );
    add_action( 'wpcf7_after_save', array( $this, 'save_settings' ) );
    add_action( 'wpcf7_before_send_mail', array( $this, 'cf7_klaviyo_submit' ) );
    add_filter( 'wpcf7_editor_panels', array( $this, 'add_form_panel' ) );
  }


  /**
   * "Array Get" Utility Function
   */
  public function arg( $array, $key, $default = null )
  {
    return isset( $array[ $key ] ) ? $array[ $key ] : $default;
  }
  
  
  /**
   * Find an assoc sub-array in a list of assoc arrays
   */
  public function arraylist_find( array $list, $key, $val )
  {
    foreach( $list as $array )
    {
      if ( $this->arg( $array, $key ) == $val ) { return $array; }    
    }
  }  


  /**
   * Get the submitted CF7 form value for a specific $prop_map object.
   */
  public function get_form_value( $formdata, $prop_map )
  {
    $val = $this->arg( $formdata, $prop_map->cf7_name );
    if ( is_array( $val ) and $prop_map->value_type == "Single Value" )
    {
      return reset( $val );
    }
    return $val;
  }


  /**
   * Handle Contact Form 7 Submit Request
   */
  public function cf7_klaviyo_submit( $wpcf7_form )
  {
    $form_post_id = $wpcf7_form->id();

    $settings_raw = get_post_meta(
      $form_post_id,
      '_cf7_klaviyo_settings',
      true
    );
    
    $form_settings = $settings_raw ? json_decode ( $settings_raw, true ) : [];

    if ( ! $form_settings ) { return; }
    if ( empty( $form_settings[ 'enabled' ] ) ) { return; }

    $list_id = $this->arg( $form_settings, 'list_id' );
    $api_public_key = $this->arg( $form_settings, 'api_public_key' );
    $api_private_key = $this->arg( $form_settings, 'api_private_key' );
    $event_name = $this->arg( $form_settings, 'event_name', 'Submit Form' );

    $concent = $this->arg( $form_settings, 'concent', [] );
    $event_prop_maps = $this->arg( $form_settings, 'event_prop_maps', [] );
    $lead_prop_maps = $this->arg( $form_settings, 'lead_prop_maps', [] );
    
    $submission = WPCF7_Submission::get_instance();
    $data = $submission->get_posted_data();
    $referer = wp_get_referer();

    $wp_klaviyo = new WP_Klaviyo( $api_public_key, $api_private_key );
    
    $email_prop_map = $this->arraylist_find( $lead_prop_maps, 'klaviyo_name', '$email' );
    if ( ! $email_prop_map )
    {
      $email_prop_map = $this->arraylist_find( $lead_prop_maps, 'klaviyo_name', 'email' );
    }
    
    if ( ! $email_prop_map ) { return; }
    else { $email_prop_map = (object) $email_prop_map; }
    
    $customer_props = array(
      'email' => $this->get_form_value( $data, $email_prop_map )
    );
    
    $event_props = array();
    foreach( $event_prop_maps as $map )
    {
      $map = (object) $map;
      $event_props[ $map->klaviyo_name ] = $this->get_form_value( $data, $map );
    }
    
    $event_props[ 'Source' ] = $referer ?: $wpcf7_form->title();

    $wp_klaviyo->track( $event_name, $customer_props, $event_props );

    
    $lead_props = array();
    foreach( $lead_prop_maps as $map )
    {
      $map = (object) $map;
      $lead_props[ $map->klaviyo_name ] = $this->get_form_value( $data, $map );
    }

    // Possible $concent values:
    // -------------------------
    // sms — Consent to receive communication via text message
    // email — Consent to receive communication via email
    // web — Consent to receive communication via website
    // directmail — Consent to receive direct mail communications
    // mobile — Consent to receive communication on mobile devices
    //
    // e.g. $concent = array( 'sms', 'email', 'mobile' );
    //
    // 'sms_consent' = true is required IN ADDITION to $concent = ['sms'] if
    // you intend to use Klaviyo SMS Features!
    //
    // 'sms_consent_timestamp' = {{unix-timestamp}}, might also be required.
    $lead_props[ '$consent' ] = $concent;
    $lead_props[ '$consent_method' ] = 'CF7 Form';
    $lead_props[ '$consent_form_id' ] = $wpcf7_form->id();
    
    $wp_klaviyo->subscribe( $list_id, $lead_props );

  }


  /**
   * Save Individual Contact Form 7 Form Klaviyo Settings.
   * Data submitted from CF7 Form Editor - Klaviyo Tab.
   */
  public function save_settings( $wpcf7_form )
  {
    $settings = array();
    
    $post_id = sanitize_text_field( $this->arg( $_POST, 'cf7k_post' ) );

    $settings[ 'enabled' ] = sanitize_text_field( $this->arg( $_POST, 'cf7k_en', 0 ) );
    $settings[ 'api_public_key' ] = sanitize_text_field( $this->arg( $_POST, 'cf7k_key' ) );
    $settings[ 'api_private_key' ] = sanitize_text_field( $this->arg( $_POST, 'cf7k_pk' ) );
    $settings[ 'list_id' ] = sanitize_text_field( $this->arg( $_POST, 'cf7k_list' ) );
    $settings[ 'event_name' ] = sanitize_text_field( $this->arg( $_POST, 'cf7k_eventname' ) );
    
    $extra = $this->arg( $_POST, 'cf7k_extra' );
    $extra = str_replace( array( "\r\n", "\n", "\r" ), '|', $extra );
    $extras = explode( '|', $extra );
    
    $settings[ 'extra' ] = sanitize_text_field( $extra );

    $event_props = $this->arg( $_POST, 'cf7k_event_props', [] );
    foreach ( $event_props as $i => $value ) {
      $event_props[ $i ] = sanitize_text_field( $value );   
    }
    $settings[ 'event_props' ] = $event_props;

    $event_prop_types = $this->arg( $_POST, 'cf7k_event_prop_types', [] );
    foreach ( $event_prop_types as $i => $value ) {
      $event_prop_types[ $i ] = sanitize_text_field( $value );   
    }
    $settings[ 'event_prop_types' ] = $event_prop_types;

    $lead_props = $this->arg( $_POST, 'cf7k_lead_props', [] );
    foreach ( $lead_props as $i => $value ) {
      $lead_props[ $i ] = sanitize_text_field( $value );   
    }
    $settings[ 'lead_props' ] = $lead_props;

    $lead_prop_types = $this->arg( $_POST, 'cf7k_lead_prop_types', [] );
    foreach ( $lead_prop_types as $i => $value ) {
      $lead_prop_types[ $i ] = sanitize_text_field( $value );   
    }
    $settings[ 'lead_prop_types' ] = $lead_prop_types;

    $concent = $this->arg( $_POST, 'cf7k_concent', [] );
    foreach ( $concent as $i => $value ) {
      $concent[ $i ] = sanitize_text_field( $value );   
    }
    $settings[ 'concent' ] = $concent;

    $tags = $wpcf7_form->scan_form_tags();
    $fieldnames = array();
    foreach ( $tags as $tag )
    {
      if ( ! empty( $tag->name ) ) { $fieldnames[] = $tag->name; }
    }

    $event_prop_maps = array();
    foreach ( $fieldnames as $i => $fieldname )
    {
      $vtype = $this->arg( $event_prop_types, $i );
      if ( $vtype == 'Exclude' ) { continue; }
      $map = new stdClass();
      $map->target = 'Track';
      $map->value_type = $vtype;
      $map->cf7_name = $fieldname;
      $map->klaviyo_name = $this->arg( $event_props, $i );
      $event_prop_maps[] = $map;
    }

    $lead_prop_maps = array();
    foreach ( $fieldnames as $i => $fieldname )
    {
      $vtype = $this->arg( $lead_prop_types, $i );
      if ( $vtype == 'Exclude' ) { continue; }
      $map = new stdClass();
      $map->target = 'Lead';
      $map->value_type = $vtype;
      $map->cf7_name = $fieldname;
      $map->klaviyo_name = $this->arg( $lead_props, $i );
      $lead_prop_maps[] = $map;
    }

    foreach ( $extras as $map_row ) {
      $map_arr = explode( ',', $map_row );
      $map_arr = array_map( 'trim', $map_arr );
      $map = new stdClass();
      $map->target = $this->arg( $map_arr, 0 );
      $map->value_type = $this->arg( $map_arr, 1 );
      $map->cf7_name = $this->arg( $map_arr, 2 );
      $map->klaviyo_name = $this->arg( $map_arr, 3 );
      if ( $map->target == 'Track' ) { $event_prop_maps[] = $map; }
      elseif ( $map->target == 'Lead' ) { $lead_prop_maps[] = $map; }
    }
    
    $settings[ 'event_prop_maps' ] = $event_prop_maps;
    $settings[ 'lead_prop_maps' ] = $lead_prop_maps;
    
    $encoded_settings = json_encode( $settings, JSON_UNESCAPED_UNICODE );

    update_post_meta( $post_id, '_cf7_klaviyo_settings', $encoded_settings );
  }


  /**
   * Add Klaviyo global settings to Contact Form 7 menu
   */
  public function add_menuitem()
  {
    add_submenu_page(
      'wpcf7',
      __( 'CF7 Klaviyo', 'cf7_klaviyo' ),
      __( 'Integration - Klaviyo', 'cf7_klaviyo' ),
      'manage_options',
      'cf7_klaviyo',
      array( $this, 'render_cf7_klaviyo_global_settings')
    );
  }


  /**
   * Add Klaviyo tab in Contact form 7 form editor.
   */
  public function add_form_panel( $panels )
  {
    $klaviyo_panel = array(
      'Klaviyo' => array(
        'title'    => __( 'Klaviyo', 'cf7_klaviyo' ),
        'callback' => array( $this, 'render_cf7_form_klaviyo_settings' )
      )
    );
    $panels = array_merge($panels, $klaviyo_panel);
    return $panels;
  }


  public function render_cf7_klaviyo_global_settings( $wpcf7 )
  {
    echo 'Welcome to Contact Form 7 - Klaviyo Integration settings!';
  }
  
  
  public function render_options( array $options, $value = null )
  {
    $html = '';
    foreach ( $options as $option )
    {
      $selected = ( $option == $value ) ? ' selected' : '';
      $html .= '<option value="' . $option . '"' . $selected . '>' . 
        $option . '</option>';
    }
    return $html;
  }


  public function render_checklist( $fieldname, array $options, array $value )
  {
    $html = '';
    foreach ( $options as $option )
    {
      $checked = in_array( $option, $value ) ? ' checked' : '';
      $html .= '<label><input type="checkbox" name="' . $fieldname . 
        '[]" value="' . $option . '"' . $checked . '><span>' . $option . '</span>' .
          '</label><br>';
    }
    return $html;
  }
  

  public function render_cf7_form_klaviyo_settings( $wpcf7_form )
  {
    $post_id = sanitize_text_field( $_GET[ 'post' ] );

    $settings_json = get_post_meta( $post_id, '_cf7_klaviyo_settings', true );
    
    $settings = $settings_json ? json_decode( $settings_json, true, 5 ) : [];

    $list_id = $this->arg( $settings, 'list_id' );
    $en = $this->arg( $settings, 'enabled'  ) ? ' checked' : '';
    $api_public_key = $this->arg( $settings, 'api_public_key' );
    $api_private_key = $this->arg( $settings, 'api_private_key' );
    $event_name = $this->arg( $settings, 'event_name', 'Submit Form' );
    $event_props = $this->arg( $settings, 'event_props', [] );
    $event_prop_types = $this->arg( $settings, 'event_prop_types', [] );
    $lead_props = $this->arg( $settings, 'lead_props', [] );
    $lead_prop_types = $this->arg( $settings, 'lead_prop_types', [] );
    $concent = $this->arg( $settings, 'concent', [] );
    $extra = str_replace( '|', PHP_EOL, $this->arg( $settings, 'extra' ) );
    $note1 = '( Klaviyo Public API Key. Example: XKSeRh )';
    $note2 = '( Klaviyo Private API Key. Example: pk_0025...e7f1e. ' .
      'Documentation <a target="_blank" href="https://help.klaviyo.com">here</a> )';
    $note3 = '( Klaviyo List ID. Example: VCUk4P )';
    $note4 = '( Klavio track event name that describes submitting this form. )';

    $type_options = array( 'Exclude', 'Single Value', 'Multi Value' );
    
    $concent_options = array( 'email', 'sms', 'web', 'directmail', 'mobile' );
    
    $tags = $wpcf7_form->scan_form_tags();
    $fieldnames = array();
    foreach ( $tags as $tag )
    {
      if ( ! empty( $tag->name ) ) { $fieldnames[] = $tag->name; }
    }
    
    $props_guide_url = 'https://help.klaviyo.com' .
      '/hc/en-us/articles/115005074627-Guide-to-Properties';

    $klaviyo_special_props = array( '$email', '$first_name',
      '$last_name', '$phone_number', '$sms_consent', '$country', '$city',
      '$address1', '$address2', '$zip', '$event_id', '$id' );

    echo
    '<style>#contact-form-editor .form-table.cf7k-table { width: auto; }',
    '#contact-form-editor .form-table.cf7k-table td { padding: 5px 10px; }',
    '#contact-form-editor .form-table.cf7k-table th { padding: 10px; width: auto; }',
    '.cf7k-divider { display: inline-block; margin: 0 0.25em; }</style>';
    
    echo
    '<h2>Klaviyo Integration Settings</h2>',
    '<input type="hidden" name="cf7k_post" value="', $post_id, '">',
    '<table class="form-table cf7k-table" role="presentation">',
    '<tr>',
      '<th scope="row"><label for="cf7k_1">Enable: </label></th>',
      '<td><input id="cf7k_1" name="cf7k_en" value="1" type="checkbox"',
         $en, '></td>',
      '<td>( Enable Klaviyo integration on this form. )</td>',
    '</tr><tr>',
      '<th scope="row"><label for="cfk_2">API Key: </label></th>',
      '<td><input type="text" id="cfk_2" name="cf7k_key" value="',
         $api_public_key, '"></td>',
      '<td>', $note1, '</td>',
    '</tr><tr>',
      '<th scope="row"><label for="cfk_3">Private Key: </label></th>',
      '<td><input type="text" id="cfk_3" name="cf7k_pk" value="',
         $api_private_key, '"></td>',
      '<td>', $note2, '</td>',
    '</tr><tr>',
      '<th scope="row"><label for="cfk_4">List ID: </label></th>',
      '<td><input type="text" id="cfk_4" name="cf7k_list" value="',
         $list_id, '"></td>',
      '<td>', $note3, '</td>',
    '</tr><tr>',
      '<th scope="row"><label for="cfk_5">Event Name: </label></th>',
      '<td><input type="text" id="cfk_5" name="cf7k_eventname" value="',
         $event_name, '"></td>',
      '<td>', $note4, '</td>',
    '</tr>',
    '</table>';
    
    echo
    '<br><br><hr><br><h2>Klaviyo API - Create / Update Leads</h2>',
    '<fieldset><legend>Link form fields to Klaviyo special "$" or custom ',
    'properties to create new lead profiles or update existing ones on submit.</legend>',
    '<ol style="margin: 0 2em 2em">',
    '<li style="color:firebrick">Please note: This integration requires a field ',
    'mapped to the "email" Klaviyo property or Klaviyo will reject your submission!</li>',
    '<li>For some reason, Klaviyo insists we use "email" instead of "$email" ',
      'when we want to add or update a lead.<br>$first_name, $last_name and ',
      '$phone_number can be either format, but any of the other special ',
      'properties<br> need to use the "$" notation. For example: $city.</li>',
    '<li>The "Map Type" setting helps to properly format field values before ',
      'transmission to Klaviyo.<br>If the value is set to EXCLUDE, the field will ',
      'NOT be sent to Klaviyo at all.'.
    '<li style="max-width:80%">Some built-in Klaviyo properties: ',
      implode( '<span class="cf7k-divider">|</span>', $klaviyo_special_props),
        '<span class="cf7k-divider">|</span>',
          '<a href="', $props_guide_url, '" target="_blank">more...</a></li>',
    '</ol>',

    '<table class="form-table props-table cf7k-table" role="presentation">',
    '<tr><th>CF7 Field</th><th>Klaviyo Property Name</th><th>Map Type</th>';
    foreach( $fieldnames as $i => $fieldname )
    {
      $prop_name = $this->arg( $lead_props, $i );
      $prop_type = $this->arg( $lead_prop_types, $i );
      $is_email = ( $fieldname == 'your-email' or $fieldname == 'email' );
      if ( $is_email and ! $prop_name )
      {
        $prop_name = 'email';
        if ( ! $prop_type ) { $prop_type = 'Single Value'; }
      }
      echo
      '<tr>',
        '<th scope="row"><label>[', $fieldname, ']</label></th>',
        '<td><input type="text" name="cf7k_lead_props[]" value="', 
           $prop_name, '"></td>',
        '<td><select name="cf7k_lead_prop_types[]" value="' . $prop_type . '">',
          $this->render_options( $type_options, $prop_type ), '</select></td>',
      '</tr>';
    }
    echo
    '</table></fieldset>';
    
    echo
    '<br><br><hr><br><h2>Klaviyo API - Track / Record Form Submits</h2>',
    '<fieldset><legend>Keep track (create snapshots) of form data submitted ',
      'over time. Assign Klaviyo property names to fields you would like track ',
      'accross multiple submits.<br>Property names can be anything, even ',
      'Klaviyo "$" names. Tracking properties will NOT update existing lead ',
      'information, nor will it create new leads!</legend>',

    '<table class="form-table props-table cf7k-table" role="presentation">',
    '<tr><th>CF7 Field</th><th>Klaviyo Property Name</th><th>Map Type</th>';
    foreach( $fieldnames as $i => $fieldname )
    {
      $prop_name = $this->arg( $event_props, $i );
      $prop_type = $this->arg( $event_prop_types, $i );
      $is_email = ( $fieldname == 'your-email' or $fieldname == 'email' );
      if ( $is_email and ! $prop_name )
      {
        $prop_name = '$email';
        if ( ! $prop_type ) { $prop_type = 'Single Value'; }
      }
      echo
      '<tr>',
        '<th scope="row"><label>[', $fieldname, ']</label></th>',
        '<td><input type="text" name="cf7k_event_props[]" value="', 
           $prop_name, '"></td>',
        '<td><select name="cf7k_event_prop_types[]" value="' . $prop_type . '">',
          $this->render_options( $type_options, $prop_type ), '</select></td>',
      '</tr>';
    }
    echo
    '</table></fieldset>';

    echo
    '<br><br><hr><br><h2>Klaviyo API - 3rd Party, Hidden &amp; Meta Fields</h2>',
    '<p>Define mappings to extra fields not detected by CF7 mail-tag scan.</p>',
    '<p>Add mappings as comma seperated lines.<br>&nbsp;&nbsp;Example: ',
      '<b>Lead, Single Value, mc4wp_checkbox, Newsletter</b><br>',
      '&nbsp;&nbsp;Example: <b>Lead, Single Value, your-phone-cf7it-country-name, $country',
    '</b><br>&nbsp;&nbsp;Example: <b>Track, Multi Value, cf7-fancy-checklist, Interests</b></p>',
    '<table class="form-table props-table cf7k-table" role="presentation" style="width:100%">',
    '<tr><td style="padding:0"><textarea rows="5" id="cf7k_6" name="cf7k_extra" ',
      'style="width:100%">', $extra, '</textarea></td></tr>',
    '</table>';

    echo
    '<br><br><hr><br><h2>Klaviyo API - Concent</h2>',
    '<p>Explicitly specify the channel(s) of communication the user concents to via this form.</p>',
    '<table class="form-table props-table cf7k-table" role="presentation">',
    '<tr>',
      '<td><fieldset>',
        '<legend class="screen-reader-text"><span>Concent Types</span></legend>',
        $this->render_checklist( 'cf7k_concent', $concent_options, $concent ),
      '</fieldset></td>',
     '</tr>',
    '</table>';
  }

} // end: Klaviyo_CF7_Integration
