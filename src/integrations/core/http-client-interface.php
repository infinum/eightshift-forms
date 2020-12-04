<?php
/**
 * Http_Client interface.
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Core;

/**
 * Http_Client interface.
 */
interface Http_Client {

  /**
   * Implementation of get request on the Http_Client.
   *
   * @param  string $url        Url to ping.
   * @param  array  $parameters (Optional) parameters for the request.
   * @return mixed
   */
  public function get( string $url, array $parameters = array());

  /**
   * Implementation of post request on the Http_Client.
   *
   * @param  string $url        Url to ping.
   * @param  array  $parameters (Optional) parameters for the request.
   * @return mixed
   */
  public function post( string $url, array $parameters = array() );
}
