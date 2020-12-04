<?php
/**
 * File missing data in filter exception
 *
 * @package Eightshift_Forms\Exception
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Exception;

use Eightshift_Libs\Exception\General_Exception;

/**
 * Unverified_Request_Exception class.
 */
class Unverified_Request_Exception extends \RuntimeException implements General_Exception {

  /**
   * Data to expose.
   *
   * @var array
   */
  private $data = [];

  /**
   * Constructs object
   *
   * @param array $data Rest response array to expose.
   */
  public function __construct( array $data = [] ) {
      $this->data = $data;
      parent::__construct( 'Unverified_Request_Exception' );
  }

  /**
   * $this->data getter.
   *
   * @return array
   */
  public function get_data(): array {
    return $this->data;
  }
}
