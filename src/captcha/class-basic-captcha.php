<?php
/**
 * Basic math captcha functionality
 *
 * @package Eightshift_Forms\Captcha
 */

declare( strict_types=1 );

namespace Eightshift_Forms\Captcha;

/**
 * Basic math captcha functionality
 */
class Basic_Captcha {

  /**
   * Key for the first number in the sum
   * 
   * @var string
   */
  const FIRST_NUMBER_KEY = 'cap_first';

  /**
   * Key for the second number in the sum
   * 
   * @var string
   */
  const SECOND_NUMBER_KEY = 'cap_second';

  /**
   * Key for the captcha result
   * 
   * @var string
   */
  const RESULT_KEY = 'cap_result';
  
  /**
   * If any of the captcha fields are submitted and inside $params array, check that the math adds up.
   *
   * @param  array $params Request parameters.
   * @return boolean
   */
  public function check_captcha_from_request_params( array $params ): bool {
    
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
