<?php


class WP_KlaviyoException extends Exception { }


class WP_Klaviyo {

  public $api_key;
  public $api_private_key;
  public $host = 'https://a.klaviyo.com/';
  protected $TRACK_ONCE_KEY = '__track_once__';


  public function __construct( $api_key, $api_private_key )
  {
    $this->api_key = $api_key;
    $this->api_private_key = $api_private_key;
  }


  public function get_lists()
  {
    $lists = array( array( 'label' => '', 'value' => '' ) );  // No value option...
    $response = $this->make_request( 'api/v2/lists', 'api_key=' . $this->api_private_key );
    $klaviyo_lists = json_decode( $response[ 'body' ] );
    foreach ( $klaviyo_lists as $list )
    {
      $lists[] = array( 'label' => $list->list_name, 'value' => $list->list_id );
    }
    return $lists;
  }


  public function track( $event, $customer_properties = array(),
    $event_properties = array(), $timestamp = null )
  {
    if (
      ( ! array_key_exists( 'email', $customer_properties )
        or empty( $customer_properties[ 'email' ] ) )
      and
      ( ! array_key_exists( '$email', $customer_properties )
        or empty( $customer_properties[ '$email' ] ) )
      and
      ( ! array_key_exists( '$id', $customer_properties )
        or empty( $customer_properties[ '$id' ] ) )
    )
    {
      throw new WP_KlaviyoException( 'You must identify a user by email or ID.' );
    }
    $params = array(
      'token' => $this->api_key,
      'event' => $event,
      'properties' => $event_properties,
      'customer_properties' => $customer_properties
    );
    if ( ! is_null( $timestamp ) )
    {
      $params[ 'time' ] = $timestamp;
    }
    $encoded_params = $this->build_params( $params );
    $response = $this->make_request( 'api/track', $encoded_params );
    return $response == '1';
  }


  public function track_once( $event, $customer_properties = array(),
    $event_properties = array(), $timestamp = null)
  {
    $event_properties[ $this->TRACK_ONCE_KEY ] = true;
    return $this->track( $event, $customer_properties, $event_properties, $timestamp );
  }


  public function identify( $properties )
  {
    if ( ( ! array_key_exists( 'email', $properties ) or empty( $properties[ 'email' ] ) )
    and ( ! array_key_exists( '$email', $properties) or empty( $properties[ '$email' ] ) ) 
    and ( ! array_key_exists( '$id', $properties) or empty( $properties[ '$id' ] ) ) ) {
      throw new WP_KlaviyoException( 'You must identify a user by email or ID.' );
    }
    $encoded_params = $this->build_params( array(
      'token' => $this->api_key,
      'properties' => $properties
    ) );
    $response = $this->make_request( 'api/identify', $encoded_params );
    return $response == '1';
  }


  public function subscribe( $list_id, $profile )
  {
    $path = 'api/v2/list/' . $list_id . '/members';
    $profiles = array( $profile );
    $post_data = array( 'api_key' => $this->api_private_key, 'profiles' => $profiles );
    $response = $this->post_request( $path, null, $post_data );
  	if ( $response[ 'response' ][ 'code' ] != 200 )
  	{
  	  error_log( __METHOD__ . '(): Could not subscribe user to mailing list' );
  	  error_log( __METHOD__ . '(): response => ' . print_r( $response, true ) );
  	}
  }


  public function build_params( $params )
  {
    return 'data=' . urlencode( base64_encode( json_encode( $params ) ) );
  }


  public function make_request( $path, $params = null )
  {
    $url = $this->host . $path;
    if ( $params ) { $url .= '?' . $params; }
    $response = wp_remote_get( $url );
    if ( is_array( $response ) and ! is_wp_error( $response ) ) {
      return $response[ 'body' ];
    }
  }


  public function post_request( $path, $params = null, $post_data = array() )
  {
    $url = $this->host . $path;
    if ( $params ) { $url .= '?' . $params; }
    $response = wp_safe_remote_post( $url, array(
      'method' => 'POST',
  	  'headers' => array( 'content-type' => 'application/json' ),
  	  'body' => json_encode( $post_data )
  	) );
    if ( is_wp_error( $response ) ) {
      $error_message = $response->get_error_message();
      throw new WP_KlaviyoException( "Something went wrong: $error_message" );
    }
    return $response;
  }  

} // end: WP_Klaviyo
