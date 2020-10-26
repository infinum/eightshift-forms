<?php
/**
 * Exception for when something is not ok with Buckaroo response.
 *
 * @package Eightshift_Forms\Exception
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Integrations\Buckaroo\Exceptions;

use Eightshift_Libs\Exception\General_Exception;

/**
 * Buckaroo_Request_Exception class.
 */
class Buckaroo_Request_Exception extends \RuntimeException implements General_Exception {

  /**
   * Message to throw.
   *
   * @var string
   */
  protected $message = '';

  /**
   * Data to expose.
   *
   * @var array
   */
  private $data = [];

  /**
   * Constructs object
   *
   * @param string $message Exception message.
   * @param array  $data (Optional) additional data we can provide.
   */
  public function __construct( string $message = '', array $data = [] ) {
      $this->message = $message;
      $this->data    = $data;
      parent::__construct( 'Buckaroo_Request_Exception' );
  }

  /**
   * Returns message and data from exception. Used in rest apis.
   *
   * @return array
   */
  public function get_exception_for_rest_response(): array {
    return [
      'message' => $this->get_message(),
      'data' => $this->get_data(),
    ];
  }

  /**
   * $this->data getter.
   *
   * @return array
   */
  public function get_data(): array {
    return $this->data;
  }

  /**
   * $this->string getter.
   *
   * @return string
   */
  public function get_message(): string {
    return $this->string;
  }
}
