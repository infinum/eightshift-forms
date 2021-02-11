<?php
/**
 * Mailerlite client implementation
 *
 * @package Eightshift_Forms\Integrations\Mailerlite
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailerlite;

use Eightshift_Forms\Hooks\Filters;
use \MailerLiteApi\MailerLite as MailerLiteClient;
use \GuzzleHttp\Client as GuzzleHttp;
use \Http\Adapter\Guzzle6\Client as Guzzle6;

/**
 * Mailerlite integration class.
 */
class Mailerlite_Client implements Mailerlite_Client_Interface {

  /**
   * Mailerlite client object.
   *
   * @var MailerLiteClient
   */
  protected $client;

  /**
   * Sets the config because we can't set config during construction (filters aren't yet registered)
   *
   * @return void
   */
  public function set_config() {
    $api_key = \has_filter( Filters::MAILERLITE ) ? \apply_filters( Filters::MAILERLITE, 'api_key' ) : '';

    $guzzle = new GuzzleHttp();
    $guzzleClient = new Guzzle6($guzzle);

    $this->client = new MailerLiteClient($api_key, $guzzleClient);
  }

  /**
   * Returns the build client
   *
   * @return MailerLiteClient
   */
  public function get_client() {
    return $this->client;
  }
}
