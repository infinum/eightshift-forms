<?php
/**
 * File missing data in filter exception
 *
 * @package EightshiftForms\Exception
 */

declare( strict_types=1 );

namespace EightshiftForms\Exception;

use Eightshift_Libs\Exception\General_Exception;

/**
 * Class Missing_Filter_Info_Exception.
 */
final class Missing_Filter_Info_Exception extends \RuntimeException implements General_Exception {

  /**
   * Create a new instance of the exception if the view file itself created
   * an exception.
   *
   * @param string $filter Which filter doesn't have all info.
   * @param string $key    Which key is missing in info provided by filter.
   *
   * @return static
   *
   * @since 0.1.0
   */
  public static function view_exception( $filter, $key ) {
    $message = sprintf(
      \esc_html__( 'Missing a required key %1$s in %2$s filter, please provide that as part of return array on that filter', 'eightshift-forms' ),
      $key,
      $filter
    );

    return new static( $message, 400 );
  }
}
