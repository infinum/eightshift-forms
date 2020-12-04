<?php
/**
 * Object representing a response from Buckaroo.
 *
 * @package Eightshift_Forms\Buckaroo
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Buckaroo;

/**
 * Factory for generating responses from Buckaroo.
 */
class Response_Factory {

  /**
   * Build Response object.
   *
   * @param array $buckaroo_params Array of Buckaroo response params.
   * @return Response
   */
  public static function build( array $buckaroo_params ): Response {
    return new Response( $buckaroo_params );
  }
}
