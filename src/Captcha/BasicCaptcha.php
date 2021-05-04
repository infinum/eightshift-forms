<?php

/**
 * Basic math captcha functionality
 *
 * @package EightshiftForms\Captcha
 */

declare(strict_types=1);

namespace EightshiftForms\Captcha;

/**
 * Basic math captcha functionality
 */
class BasicCaptcha
{

	/**
	 * Key for the first number in the sum
	 *
	 * @var string
	 */
	public const FIRST_NUMBER_KEY = 'cap_first';

	/**
	 * Key for the second number in the sum
	 *
	 * @var string
	 */
	public const SECOND_NUMBER_KEY = 'cap_second';

	/**
	 * Key for the captcha result
	 *
	 * @var string
	 */
	public const RESULT_KEY = 'cap_result';

	/**
	 * If any of the captcha fields are submitted and inside $params array, check that the math adds up.
	 *
	 * @param  array $params Request parameters.
	 * @return boolean
	 */
	public function checkCaptchaFromRequestParams(array $params): bool
	{

	  // First let's see if captcha fields are in request params. If not, just return true.
		if (
			! isset($params[self::FIRST_NUMBER_KEY])
			&& ! isset($params[self::SECOND_NUMBER_KEY])
			&& ! isset($params[self::RESULT_KEY])
		) {
			return true;
		}

	  // Now let's make sure we have all the required fields otherwise there is some tampering of form params
	  // going on and we consider the captcha as failed.
		if (
			empty($params[self::FIRST_NUMBER_KEY])
			|| empty($params[self::SECOND_NUMBER_KEY])
			|| empty($params[self::RESULT_KEY])
		) {
			return false;
		}

	  // Now let's make sure the captcha is correct.
		if ((int) $params[self::FIRST_NUMBER_KEY] + (int) $params[self::SECOND_NUMBER_KEY] === (int) $params[self::RESULT_KEY]) {
			return true;
		}

		return false;
	}
}
