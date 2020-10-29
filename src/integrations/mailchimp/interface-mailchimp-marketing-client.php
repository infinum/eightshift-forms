<?php
/**
 * Mailchimp_Marketing_Client_Interface interface
 *
 * @package Eightshift_Forms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailchimp;

/**
 * Mailchimp_Marketing_Client_Interface interface.
 */
interface Mailchimp_Marketing_Client_Interface {

  /**
   * Returns the build client
   *
   * @return object
   */
  public function get_client();
}
