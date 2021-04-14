<?php
/**
 * Client_Interface interface
 *
 * @package Eightshift_Forms\Integrations
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations;

use \MailerLiteApi\MailerLite;
use \MailchimpMarketing\ApiClient as MarketingApiClient;

/**
 * Client_Interface interface.
 */
interface Client_Interface {

  /**
   * Returns the build client
   *
   * @return mixed
   */
  public function get_client();

  /**
   * Sets the config because we can't set config during construction (filters aren't yet registered)
   *
   * @return void
   */
  public function set_config();
}
