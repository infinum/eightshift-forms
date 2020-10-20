<?php
/**
 * Invalid_Buckaroo_Response_Exception class.
 *
 * @package Eightshift_Forms\Exception
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Buckaroo;

use Eightshift_Libs\Exception\General_Exception;

/**
 * Invalid_Buckaroo_Response_Exception class.
 */
class Invalid_Buckaroo_Response_Exception extends \RuntimeException implements General_Exception {

  /**
   * Message to throw.
   *
   * @var string
   */
  private $error_message = '';

  /**
   * Constructs object
   *
   * @param string $error_message Message to throw.
   */
  public function __construct( string $error_message ) {
      $this->error_message = $error_message;
      parent::__construct( 'Invalid_Buckaroo_Response_Exception' );
  }

  /**
   * $this->error_message getter.
   *
   * @return string
   */
  public function get_error_message(): string {
    return $this->error_message;
  }
}
