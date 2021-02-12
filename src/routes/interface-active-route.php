<?php
/**
 * Active_Route interface
 *
 * @package Eightshift_Forms\Integrations\Mailchimp
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Rest;

/**
 * Interface for routes on which you can read their entire uri.
 */
interface Active_Route {

  /**
   * Returns the build client
   *
   * @return string
   */
  public function get_route_uri(): string;
}
