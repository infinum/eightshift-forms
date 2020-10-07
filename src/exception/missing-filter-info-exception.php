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
 * Class Missing_Filter_Info_Exception.
 */
final class Missing_Filter_Info_Exception extends \RuntimeException implements General_Exception {

  /**
   * Create a new instance of the exception if the view file itself created
   * an exception.
   *
   * @param string     $uri       URI of the file that is not accessible or
   *                              not readable.
   * @param \Exception $exception Exception that was thrown by the view file.
   *
   * @return static
   *
   * @since 0.1.0
   */
  public static function view_exception( $filter, $key ) {
    $message = sprintf(
      \esc_html__( 'Missing a required key %s in %s filter, please provide that as part of return array on that filter', 'eightshift-libs' ),
      $key,
      $filter,
    );

    return new static( $message, 400 );
  }
}