<?php
/**
 * Mailerlite_Client_Interface interface
 *
 * @package Eightshift_Forms\Integrations\Mailerlite
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Mailerlite;

use \MailerLiteApi\MailerLite;

/**
 * Mailerlite_Client_Interface interface.
 */
interface Mailerlite_Client_Interface {

  /**
   * Returns the build client
   *
   * @return MailerLite
   */
  public function get_client();

  /**
   * Sets the config because we can't set config during construction (filters aren't yet registered)
   *
   * @return void
   */
  public function set_config();
}
