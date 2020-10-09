<?php
/**
 * Guzzle client, implementation of Http_Client.
 *
 * @package Eightshift_Forms\Integrations\Core
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Core;

use GuzzleHttp\ClientInterface;

/**
 * Guzzle client, implementation of Http_Client.
 */
class Guzzle_Client implements Http_Client {

  /**
   * Constructs object.
   *
   * @param ClientInterface $client DI injected Guzzle client.
   */
  public function __construct( ClientInterface $client ) {
    $this->client = $client;
  }

  /**
   * Implementation of get request on the Http_Client.
   *
   * @param  string $url        Url to ping.
   * @param  array  $parameters (Optional) parameters for the request.
   * @return mixed
   */
  public function get( string $url, array $parameters = array() ) {
    return $this->client->get( $url, $parameters );
  }

  /**
   * Implementation of post request on the Http_Client.
   *
   * @param  string $url        Url to ping.
   * @param  array  $parameters (Optional) parameters for the request.
   * @return mixed
   */
  public function post( string $url, array $parameters = array() ) {
    return $this->client->post( $url, $parameters );
  }

  /**
   * Implementation of post request on the Http_Client.
   *
   * @param  string $url        Url to ping.
   * @param  array  $parameters (Optional) parameters for the request.
   * @return mixed
   */
  public function patch( string $url, array $parameters = array() ) {
    return $this->client->patch( $url, $parameters );
  }
}
