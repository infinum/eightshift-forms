<?php
/**
 * Mailerlite client implementation
 *
 * @package Eightshift_Forms\Integrations\Mailerlite
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailerlite;

use Eightshift_Forms\Hooks\Filters;
use Eightshift_Forms\Integrations\Client_Interface;
use \MailerLiteApi\MailerLite;
use \GuzzleHttp\Client as GuzzleHttp;
use \Http\Adapter\Guzzle6\Client as Guzzle6;
use \MailchimpMarketing\ApiClient as MarketingApiClient;

/**
 * Mailerlite integration class.
 */
class Mailerlite_Client implements Client_Interface {

  /**
   * Mailerlite client object.
   *
   * @var MailerLite
   */
  private $client;

  /**
   * Sets the config because we can't set config during construction (filters aren't yet registered)
   *
   * @return void
   */
  public function set_config() {
    $api_key = \has_filter( Filters::MAILERLITE ) ? \apply_filters( Filters::MAILERLITE, 'api_key' ) : '';

    $guzzle        = new GuzzleHttp();
    $guzzle_client = new Guzzle6( $guzzle );

    $this->client = new MailerLite( $api_key, $guzzle_client );
  }

  /**
   * Returns the build client
   *
   * @return mixed
   */
  public function get_client() {
    return $this->client;
  }
}
