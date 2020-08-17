<?php
/**
 * Basic captcha functionality
 *
 * @package Eightshift_Forms\Captcha
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Captcha;

/**
 * The plugin activation class.
 */
class Basic_Captcha {
  const FIRST_NUMBER_KEY = 'cap_first';
  const SECOND_NUMBER_KEY = 'cap_second';
  const RESULT_KEY = 'cap_result';
  
  public function check_captcha_from_request_params( array $params ): bool {

    error_log(print_r($params, true));
    
    // First let's see if captcha fields are in request params. If not, just return true.
    if (
      ! isset( $params[ self::FIRST_NUMBER_KEY ] )
      && ! isset( $params[ self::SECOND_NUMBER_KEY ] )
      && ! isset( $params[ self::RESULT_KEY ] )
    ) {
      return true;
    }

    // Now let's make sure we have all the required fields otherwise there is some tampering of form params
    // going on and we consider the captcha as failed.
    if (
      empty( $params[ self::FIRST_NUMBER_KEY ] )
      || empty( $params[ self::SECOND_NUMBER_KEY ] )
      || empty( $params[ self::RESULT_KEY ] )
    ) {
      return false;
    }

    // Now let's make sure the captcha is correct
    if ( (int) $params[ self::FIRST_NUMBER_KEY ] + (int) $params[ self::SECOND_NUMBER_KEY ] === (int) $params[ self::RESULT_KEY ] ) {
      return true;
    }

    return false;
  }
} 
