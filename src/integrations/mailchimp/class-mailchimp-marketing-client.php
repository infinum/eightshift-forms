<?php

/**
 * Mailchimp marketing client implementation
 *
 * @package Eightshift_Forms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailchimp;

use Eightshift_Forms\Hooks\Filters;
use \MailchimpMarketing\ApiClient as MarketingApiClient;

/**
 * Mailchimp integration class.
 */
class Mailchimp_Marketing_Client implements Mailchimp_Marketing_Client_Interface {

  /**
   * Constructs object
   */
  public function __construct() {
    $this->client = new MarketingApiClient();
    $this->client->setConfig( [
      'apiKey' => \has_filter( Filters::MAILCHIMP ) ? \apply_filters( Filters::MAILCHIMP, 'api_key' ) : '',
      'server' => \has_filter( Filters::MAILCHIMP ) ? \apply_filters( Filters::MAILCHIMP, 'server' ) : '',
    ] );
  }

  /**
   * Returns the build client
   *
   * @return object
   */
  public function get_client() {
    return $this->client;
  }
}
